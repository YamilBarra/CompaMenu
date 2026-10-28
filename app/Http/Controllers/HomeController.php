<?php

namespace App\Http\Controllers;

use App\Models\Local;
use App\Models\Menu;
use App\Models\Remate;
use App\Support\Texto;
use Illuminate\Http\Request;

/** Inicio: portada con el buscador de menús (filtros por precio, tipo, distancia, nombre, disponibilidad y remates). */
class HomeController extends Controller
{
    /**
     * Página de inicio con el buscador de menús.
     *
     * Lee los filtros de la URL (precio, tipo, distancia, q, disp, rem), los lleva a valores permitidos y consulta la base.
     * Si la petición viene de fetch() (cabecera X-Requested-With) devuelve solo las tarjetas en JSON;
     * si no, devuelve la página completa. Así el buscador funciona con y sin JavaScript.
     *
     * @see https://developer.mozilla.org/es/docs/Web/API/Fetch_API
     * Ruta: GET / (inicio).
     */
    public function index(Request $request)
    {
        $tipo = (string) $request->query('tipo', 'todos');
        $distancia = (int) $request->query('distancia', 999999);

        $filtros = [
            'precio' => max(1, min(30, (float) $request->query('precio', 7.5))),
            'tipo' => in_array($tipo, array_keys(Menu::TIPOS), true) ? $tipo : 'todos',
            'distancia' => in_array($distancia, [200, 500, 1000], true) ? $distancia : 999999,
            'q' => mb_substr(Texto::limpio((string) $request->query('q', '')) ?? '', 0, 60),
            'disp' => $request->query('disp', '1') !== '0',   // solo menús disponibles
            'rem' => $request->boolean('rem'),                // solo menús con remate
        ];

        $menus = $this->buscar($filtros);

        // El buscador pide los resultados con fetch (JavaScript) y recibe solo las tarjetas
        if ($request->ajax()) {
            return response()->json([
                'html' => view('menus._resultados', ['menus' => $menus])->render(),
                'total' => $menus->count(),
                'resumen' => $menus->count().' resultado(s) hasta S/. '.number_format($filtros['precio'], 2),
            ]);
        }

        return view('inicio', [
            'menus' => $menus,
            'filtros' => $filtros,
            'totalLocales' => Local::where('activo', true)->count(),
            'totalRemates' => Remate::vigentes()->count(),
        ]);
    }

    /** Consulta a la base de datos (Eloquent usa sentencias preparadas, así que no hay SQL Injection). */
    private function buscar(array $f)
    {
        return Menu::query()
            ->with(['local' => fn ($q) => $q->conPuntaje(), 'remateActivo'])
            ->whereHas('local', function ($q) use ($f) {
                $q->where('activo', true);
                if ($f['distancia'] < 999999) {
                    $q->where('distancia_campus', '<=', $f['distancia']);
                }
            })
            ->where('precio', '<=', $f['precio'])
            ->when($f['disp'], fn ($q) => $q->where('disponible', true))
            ->when($f['tipo'] !== 'todos', fn ($q) => $q->where('tipo', $f['tipo']))
            ->when($f['q'] !== '', function ($q) use ($f) {
                $texto = '%'.Texto::paraLike(mb_strtolower($f['q'])).'%';
                $q->where(function ($q) use ($texto) {
                    $q->whereRaw('LOWER(nombre) LIKE ?', [$texto])
                        ->orWhereHas('local', fn ($l) => $l->whereRaw('LOWER(nombre) LIKE ?', [$texto]));
                });
            })
            ->when($f['rem'], fn ($q) => $q->whereHas('remates', fn ($r) => $r->where('activo', true)->where('porciones', '>', 0)))
            ->orderBy('precio')
            ->orderBy('nombre')
            ->get();
    }
}
