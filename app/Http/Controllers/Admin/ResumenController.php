<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Local;
use App\Models\Menu;
use App\Models\Remate;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Valoracion;
use App\Support\CacheConsulta;

/** Panel administrativo: resumen con las métricas del documento (sección 6.6) y gráficos. */
class ResumenController extends Controller
{
    /** Colores de la paleta andina para cada estado de reserva (coinciden con estilos.css). */
    private const COLOR_ESTADO = [
        'pendiente' => '#f4b41a',
        'confirmada' => '#1b9aaa',
        'completada' => '#2e7d4f',
        'cancelada' => '#9c8a83',
        'rechazada' => '#b5121b',
    ];

    private const DIAS = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

    /**
     * Calcula las métricas del panel (sección 6.6 de la propuesta): estudiantes, establecimientos, menús, remates,
     * valoraciones, promedio de estrellas y reservas. El promedio solo cuenta valoraciones visibles.
     *
     * Optimización (Semana 11):
     *  - Los conteos de los gráficos se hacen en la base con COUNT(*) ... GROUP BY (antes se traían todas las
     *    filas con pluck() y se contaban en PHP).
     *  - Métricas y gráficos se guardan 2 minutos en caché; el caché se invalida solo cuando cambian los datos
     *    (ver CacheConsulta y AppServiceProvider).
     *  - Las listas "últimas reservas/reseñas" traen solo las columnas que usa la vista.
     * Ruta: GET /admin.
     */
    public function index()
    {
        ['metricas' => $metricas, 'graficos' => $graficos] = CacheConsulta::recordar('admin.resumen', 120, function () {
            $porEstado = Reserva::selectRaw('estado, COUNT(*) as total')->groupBy('estado')->pluck('total', 'estado');

            return [
                'metricas' => [
                    'estudiantes' => User::where('rol', User::ROL_ESTUDIANTE)->count(),
                    'establecimientos' => Local::count(),
                    'menus' => Menu::count(),
                    'remates' => Remate::count(),
                    'valoraciones' => Valoracion::count(),
                    'promedio' => round((float) Valoracion::where('visible', true)->avg('puntaje'), 1),
                    'reservas' => (int) $porEstado->sum(),            // total = suma de los grupos, sin otra consulta
                    'pendientes' => (int) ($porEstado['pendiente'] ?? 0),
                ],
                'graficos' => [
                    'estados' => $this->reservasPorEstado($porEstado),
                    'dias' => $this->reservasUltimosDias(7),
                    'tipos' => $this->menusPorTipo(),
                    'puntajes' => $this->distribucionPuntajes(),
                    'locales' => $this->localesMasReservados(),
                ],
            ];
        });

        $ultimasReservas = Reserva::with(['user:id,name', 'menu:id,nombre', 'local:id,nombre'])->latest()->take(5)->get();
        $ultimasResenas = Valoracion::with(['user:id,name', 'local:id,nombre'])->latest()->take(5)->get();

        return view('admin.resumen', compact('metricas', 'ultimasReservas', 'ultimasResenas', 'graficos'));
    }

    /** Reservas agrupadas por estado, en el orden de Reserva::ESTADOS. Recibe el conteo ya calculado en la base. */
    private function reservasPorEstado($conteo): array
    {
        return collect(Reserva::ESTADOS)->map(fn (string $nombre, string $clave) => [
            'clave' => $clave,
            'nombre' => $nombre,
            'total' => (int) ($conteo[$clave] ?? 0),
            'color' => self::COLOR_ESTADO[$clave] ?? '#9c8a83',
        ])->values()->all();
    }

    /** Reservas creadas en cada uno de los últimos $n días (incluye los días con 0). */
    private function reservasUltimosDias(int $n): array
    {
        $dias = collect(range($n - 1, 0))->map(fn (int $atras) => now()->subDays($atras)->startOfDay());

        // COUNT(*) por día en la base (DATE() existe en MySQL y en SQLite)
        $conteo = Reserva::where('created_at', '>=', $dias->first())
            ->selectRaw('DATE(created_at) as dia, COUNT(*) as total')
            ->groupBy('dia')
            ->pluck('total', 'dia');

        return $dias->map(fn ($dia) => [
            'etiqueta' => self::DIAS[$dia->dayOfWeek],
            'fecha' => $dia->format('d/m'),
            'total' => (int) ($conteo[$dia->toDateString()] ?? 0),
            'hoy' => $dia->isToday(),
        ])->all();
    }

    /** Menús publicados por tipo (criollo, andino, vegetariano, proteico). */
    private function menusPorTipo(): array
    {
        $conteo = Menu::selectRaw('tipo, COUNT(*) as total')->groupBy('tipo')->pluck('total', 'tipo');
        $colores = ['criollo' => '#b5121b', 'andino' => '#f4b41a', 'vegetariano' => '#2e7d4f', 'proteico' => '#1b9aaa'];

        return collect(Menu::TIPOS)->map(fn (string $nombre, string $clave) => [
            'nombre' => $nombre,
            'total' => (int) ($conteo[$clave] ?? 0),
            'color' => $colores[$clave] ?? '#c2185b',
        ])->values()->all();
    }

    /** Cuántas valoraciones visibles hay de 5 a 1 estrellas. */
    private function distribucionPuntajes(): array
    {
        $conteo = Valoracion::where('visible', true)->selectRaw('puntaje, COUNT(*) as total')->groupBy('puntaje')->pluck('total', 'puntaje');

        return collect(range(5, 1))->map(fn (int $p) => [
            'nombre' => $p.' ★',
            'total' => (int) ($conteo[$p] ?? 0),
            'color' => '#f4b41a',
        ])->all();
    }

    /** Los 5 locales con más reservas (solo los que tienen al menos una). */
    private function localesMasReservados(): array
    {
        return Local::select('id', 'nombre')
            ->withCount('reservas')
            ->has('reservas')
            ->orderByDesc('reservas_count')
            ->take(5)
            ->get()
            ->map(fn (Local $l) => ['nombre' => $l->nombre, 'total' => (int) $l->reservas_count, 'color' => '#c2185b'])
            ->all();
    }
}
