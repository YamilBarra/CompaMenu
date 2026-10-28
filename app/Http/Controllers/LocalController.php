<?php

namespace App\Http\Controllers;

use App\Models\Local;
use App\Support\Texto;
use Illuminate\Http\Request;

/** Módulo de locales: lista de pensiones, quioscos y comedores y la página de cada local. */
class LocalController extends Controller
{
    /**
     * Lista de locales activos con puntaje, filtro por tipo y búsqueda por nombre.
     * Ruta: GET /locales (locales.index).
     */
    public function index(Request $request)
    {
        $tipo = (string) $request->query('tipo', '');
        $q = Texto::limpio((string) $request->query('q', '')) ?? '';

        $locales = Local::query()
            ->where('activo', true)
            ->conPuntaje()
            ->withCount(['menus as menus_disponibles' => fn ($m) => $m->where('disponible', true)])
            ->when(array_key_exists($tipo, Local::TIPOS), fn ($query) => $query->where('tipo_local', $tipo))
            ->when($q !== '', fn ($query) => $query->whereRaw('LOWER(nombre) LIKE ?', ['%'.Texto::paraLike(mb_strtolower($q)).'%']))
            ->orderBy('distancia_campus')
            ->get();

        return view('locales.index', ['locales' => $locales, 'tipo' => $tipo, 'q' => $q]);
    }

    /**
     * Página de un local: información, menús (los disponibles primero) y reseñas recientes.
     * Un local oculto por el administrador responde 404.
     * Ruta: GET /locales/{local}.
     */
    public function show(Local $local)
    {
        abort_unless($local->activo, 404);

        $local = Local::conPuntaje()->findOrFail($local->id);
        $menus = $local->menus()
            ->with(['local' => fn ($q) => $q->conPuntaje(), 'remateActivo'])
            ->orderByDesc('disponible')
            ->orderBy('precio')
            ->get();
        $resenas = $local->valoracionesVisibles()->with('user')->latest()->take(5)->get();

        return view('locales.show', compact('local', 'menus', 'resenas'));
    }
}
