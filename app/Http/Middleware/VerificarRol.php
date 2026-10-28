<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Control de acceso por rol (sección 6.3 del documento).
 * Uso en rutas: ->middleware('rol:establecimiento')  o  ->middleware('rol:estudiante,administrador')
 */
class VerificarRol
{
    /**
     * Verifica la sesión, que la cuenta siga activa y que el rol esté entre los permitidos.
     *
     * - Sin sesión: redirige al ingreso (y recuerda la URL pedida).
     * - Cuenta desactivada: cierra la sesión.
     * - Rol no permitido: responde 403.
     *
     * Se usa como ->middleware('rol:estudiante,administrador') (alias registrado en bootstrap/app.php).
     * @see https://laravel.com/docs/middleware
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            return redirect()->guest(route('login'));
        }

        // Si el administrador desactivó la cuenta, se cierra la sesión
        if (! $usuario->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Tu cuenta está desactivada. Comunícate con el administrador.']);
        }

        if (! in_array($usuario->rol, $roles, true)) {
            abort(403, 'Tu rol no tiene permiso para entrar a esta sección.');
        }

        return $next($request);
    }
}
