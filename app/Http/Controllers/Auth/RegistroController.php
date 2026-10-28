<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Local;
use App\Models\User;
use App\Support\Texto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Registro de estudiantes y de establecimientos (el administrador no se registra: lo crea el seeder). */
class RegistroController extends Controller
{
    /**
     * Muestra el formulario de registro; ?rol=establecimiento deja marcada esa opción.
     * Ruta: GET /registro.
     */
    public function show(Request $request)
    {
        $rol = $request->query('rol') === 'establecimiento' ? 'establecimiento' : 'estudiante';

        return view('auth.registro', compact('rol'));
    }

    /**
     * Crea la cuenta. Las reglas dependen del rol elegido: un establecimiento también debe enviar los datos de su local.
     * Usuario y local se crean en una transacción (o se guardan los dos o ninguno).
     * El rol administrador no se acepta aquí: Rule::in solo permite estudiante y establecimiento.
     *
     * @see https://laravel.com/docs/validation
     * Ruta: POST /registro.
     */
    public function store(Request $request)
    {
        $reglas = [
            'name' => ['required', 'string', 'min:3', 'max:60'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'rol' => ['required', Rule::in([User::ROL_ESTUDIANTE, User::ROL_ESTABLECIMIENTO])],
            'telefono' => ['nullable', 'regex:/^[0-9+\s-]{6,20}$/'],
        ];

        // Si es un establecimiento, también pedimos los datos de su local
        if ($request->input('rol') === User::ROL_ESTABLECIMIENTO) {
            $reglas += [
                'local_nombre' => ['required', 'string', 'min:3', 'max:60'],
                'tipo_local' => ['required', Rule::in(array_keys(Local::TIPOS))],
                'direccion' => ['nullable', 'string', 'max:120'],
                'distancia_campus' => ['required', 'integer', 'between:0,5000'],
            ];
        }

        $datos = $request->validate($reglas);

        $usuario = DB::transaction(function () use ($datos) {
            $usuario = User::create([
                'name' => Texto::limpio($datos['name']),
                'email' => Str::lower($datos['email']),
                'password' => $datos['password'], // el modelo la guarda con hash
                'rol' => $datos['rol'],
                'telefono' => $datos['telefono'] ?? null,
                'activo' => true,
            ]);

            if ($usuario->esEstablecimiento()) {
                Local::create([
                    'user_id' => $usuario->id,
                    'nombre' => Texto::limpio($datos['local_nombre']),
                    'tipo_local' => $datos['tipo_local'],
                    'direccion' => Texto::limpio($datos['direccion'] ?? null),
                    'distancia_campus' => $datos['distancia_campus'],
                    'telefono' => $datos['telefono'] ?? null,
                    'activo' => true,
                ]);
            }

            return $usuario;
        });

        Auth::login($usuario);
        $request->session()->regenerate();

        $mensaje = $usuario->esEstablecimiento()
            ? '¡Bienvenido! Tu local ya está registrado. Agrega tu primer menú.'
            : '¡Cuenta creada! Ya puedes reservar platos y dejar reseñas.';

        return redirect()->intended(LoginController::destino($usuario))->with('exito', $mensaje);
    }
}
