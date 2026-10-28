<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;

/** Supervisión de menús publicados. */
class MenuController extends Controller
{
    /**
     * Lista paginada de todos los menús publicados, para supervisión.
     * Ruta: GET /admin/menus.
     */
    public function index()
    {
        $menus = Menu::with('local')->latest()->paginate(12);

        return view('admin.menus', compact('menus'));
    }

    /**
     * Elimina un menú y su foto subida. Solo se borran archivos de public/uploads/menus/
     * (las fotos de ejemplo de public/img nunca se tocan).
     * Ruta: DELETE /admin/menus/{menu}.
     */
    public function destroy(Menu $menu)
    {
        if ($menu->foto && str_starts_with($menu->foto, 'uploads/menus/') && is_file(public_path($menu->foto))) {
            @unlink(public_path($menu->foto));
        }
        $menu->delete();

        return back()->with('exito', 'Eliminamos el menú.');
    }
}
