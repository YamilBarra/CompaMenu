<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Local;

/** Gestión de establecimientos. */
class LocalController extends Controller
{
    /**
     * Lista los establecimientos con su puntaje promedio y el total de menús y remates.
     * Ruta: GET /admin/locales (admin.locales.index) · Rol: administrador.
     */
    public function index()
    {
        $locales = Local::query()
            ->with('user')
            ->conPuntaje()
            ->withCount(['menus', 'remates'])
            ->orderBy('nombre')
            ->paginate(12);

        return view('admin.locales', compact('locales'));
    }

    /**
     * Oculta o vuelve a mostrar un local. Un local oculto desaparece del buscador y de su página (responde 404),
     * pero conserva sus datos y reservas.
     * Ruta: PATCH /admin/locales/{local}/alternar.
     */
    public function alternar(Local $local)
    {
        $local->update(['activo' => ! $local->activo]);

        return back()->with('exito', $local->activo ? 'El local vuelve a estar visible.' : 'Ocultamos el local de la plataforma.');
    }

    /**
     * Elimina el local. Las claves foráneas con ON DELETE CASCADE borran también sus menús, platos, remates y reservas.
     * Ruta: DELETE /admin/locales/{local}.
     */
    public function destroy(Local $local)
    {
        $local->delete();

        return back()->with('exito', 'Eliminamos el local y sus menús.');
    }
}
