<?php

namespace App\Http\Controllers;

use App\Models\Menu;

/** Detalle público de un menú (caso de uso 1: "Selecciona una opción para ver sus detalles"). */
class MenuController extends Controller
{
    /**
     * Detalle público de un menú con sus platos, el remate vigente (si hay) y reseñas del local
     * (caso de uso 1 de la propuesta: "selecciona una opción para ver sus detalles").
     * Ruta: GET /menus/{menu}.
     */
    public function show(Menu $menu)
    {
        abort_unless($menu->local()->where('activo', true)->exists(), 404);

        $menu->load([
            'local' => fn ($q) => $q->conPuntaje(),
            'platos',
            'remateActivo',
        ]);

        $resenas = $menu->local->valoracionesVisibles()->with('user')->latest()->take(3)->get();

        return view('menus.show', compact('menu', 'resenas'));
    }
}
