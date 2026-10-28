<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Texto;
use Illuminate\Http\Request;

/** Gestión de usuarios: consultar, desactivar/activar y eliminar. */
class UsuarioController extends Controller
{
    /**
     * Lista paginada de usuarios con filtro por rol y búsqueda por nombre o correo.
     * El texto se limpia (Texto::limpio) y se escapan los comodines de LIKE (Texto::paraLike).
     * Ruta: GET /admin/usuarios.
     */
    public function index(Request $request)
    {
        $rol = (string) $request->query('rol', '');
        $q = Texto::limpio((string) $request->query('q', '')) ?? '';

        $usuarios = User::query()
            ->with('local')
            ->when(in_array($rol, [User::ROL_ESTUDIANTE, User::ROL_ESTABLECIMIENTO, User::ROL_ADMIN], true), fn ($query) => $query->where('rol', $rol))
            ->when($q !== '', function ($query) use ($q) {
                $texto = '%'.Texto::paraLike(mb_strtolower($q)).'%';
                $query->where(fn ($w) => $w->whereRaw('LOWER(name) LIKE ?', [$texto])->orWhereRaw('LOWER(email) LIKE ?', [$texto]));
            })
            ->orderBy('rol')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.usuarios', compact('usuarios', 'rol', 'q'));
    }

    /**
     * Activa o desactiva una cuenta. El middleware VerificarRol cierra la sesión de una cuenta desactivada
     * en su siguiente petición. Un administrador no puede desactivarse a sí mismo (se quedaría sin acceso).
     * Ruta: PATCH /admin/usuarios/{usuario}/alternar.
     */
    public function alternar(Request $request, User $usuario)
    {
        if ($usuario->id === $request->user()->id) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $usuario->update(['activo' => ! $usuario->activo]);

        return back()->with('exito', $usuario->activo ? 'Activaste la cuenta.' : 'Desactivaste la cuenta.');
    }

    /**
     * Elimina un usuario (y por cascada su local, reseñas y reservas).
     * No permite que el administrador se elimine a sí mismo.
     * Ruta: DELETE /admin/usuarios/{usuario}.
     */
    public function destroy(Request $request, User $usuario)
    {
        if ($usuario->id === $request->user()->id) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $usuario->delete(); // también se borran su local, menús y reseñas (claves foráneas en cascada)

        return back()->with('exito', 'Eliminamos al usuario.');
    }
}
