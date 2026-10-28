<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Inicio y cierre de sesión. Laravel compara la contraseña con su hash (equivale a password_verify). */
class LoginController extends Controller
{
    /**
     * Muestra el formulario de ingreso.
     * Ruta: GET /ingresar (login) · Solo invitados (middleware guest).
     */
    public function show()
    {
        return view('auth.login');
    }

    /**
     * Valida las credenciales e inicia sesión.
     *
     * Seguridad aplicada:
     * 1. Límite de 5 intentos por minuto para cada combinación correo + IP (RateLimiter).
     * 2. Auth::attempt compara contra el hash bcrypt y exige activo = true.
     * 3. Mensaje de error genérico: no revela si el correo existe.
     * 4. Se regenera el ID de sesión para evitar la fijación de sesión.
     *
     * @see https://laravel.com/docs/authentication
     * @see https://laravel.com/docs/rate-limiting
     * Ruta: POST /ingresar.
     */
    public function login(Request $request)
    {
        $datos = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Límite de intentos: 5 por minuto para el mismo correo y la misma IP
        $clave = Str::lower($datos['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($clave, 5)) {
            throw ValidationException::withMessages([
                'email' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($clave)]),
            ]);
        }

        if (! Auth::attempt(['email' => $datos['email'], 'password' => $datos['password'], 'activo' => true], $request->boolean('recordar'))) {
            RateLimiter::hit($clave, 60);

            // Si la clave es correcta pero la cuenta está desactivada, se lo decimos
            $usuario = User::where('email', $datos['email'])->first();
            if ($usuario && ! $usuario->activo && Hash::check($datos['password'], $usuario->password)) {
                throw ValidationException::withMessages(['email' => 'Tu cuenta está desactivada. Comunícate con el administrador.']);
            }

            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        RateLimiter::clear($clave);
        $request->session()->regenerate(); // evita la fijación de sesión

        return redirect()->intended($this->destino($request->user()));
    }

    /**
     * Cierra la sesión: invalida la sesión actual y genera un token CSRF nuevo.
     * Ruta: POST /salir (es POST y no GET para que un enlace externo no pueda cerrar la sesión).
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('inicio')->with('exito', 'Cerraste sesión.');
    }

    /** Cada rol aterriza en su propio panel. */
    public static function destino(User $usuario): string
    {
        return match (true) {
            $usuario->esAdmin() => route('admin.resumen'),
            $usuario->esEstablecimiento() => route('panel.menus.index'),
            default => route('inicio'),
        };
    }
}
