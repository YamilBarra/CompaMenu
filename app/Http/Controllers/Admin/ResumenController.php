<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Local;
use App\Models\Menu;
use App\Models\Remate;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Valoracion;

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
     * Además prepara los datos de los gráficos (todo con Eloquent y colecciones, sin SQL crudo).
     * Ruta: GET /admin.
     */
    public function index()
    {
        $metricas = [
            'estudiantes' => User::where('rol', User::ROL_ESTUDIANTE)->count(),
            'establecimientos' => Local::count(),
            'menus' => Menu::count(),
            'remates' => Remate::count(),
            'valoraciones' => Valoracion::count(),
            'promedio' => round((float) Valoracion::where('visible', true)->avg('puntaje'), 1),
            'reservas' => Reserva::count(),
            'pendientes' => Reserva::where('estado', 'pendiente')->count(),
        ];

        $ultimasReservas = Reserva::with(['user', 'menu', 'local'])->latest()->take(5)->get();
        $ultimasResenas = Valoracion::with(['user', 'local'])->latest()->take(5)->get();

        $graficos = [
            'estados' => $this->reservasPorEstado(),
            'dias' => $this->reservasUltimosDias(7),
            'tipos' => $this->menusPorTipo(),
            'puntajes' => $this->distribucionPuntajes(),
            'locales' => $this->localesMasReservados(),
        ];

        return view('admin.resumen', compact('metricas', 'ultimasReservas', 'ultimasResenas', 'graficos'));
    }

    /** Reservas agrupadas por estado, en el orden de Reserva::ESTADOS. */
    private function reservasPorEstado(): array
    {
        $conteo = Reserva::pluck('estado')->countBy();

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

        $conteo = Reserva::where('created_at', '>=', $dias->first())
            ->pluck('created_at')
            ->countBy(fn ($fecha) => $fecha->toDateString());

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
        $conteo = Menu::pluck('tipo')->countBy();
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
        $conteo = Valoracion::where('visible', true)->pluck('puntaje')->countBy();

        return collect(range(5, 1))->map(fn (int $p) => [
            'nombre' => $p.' ★',
            'total' => (int) ($conteo[$p] ?? 0),
            'color' => '#f4b41a',
        ])->all();
    }

    /** Los 5 locales con más reservas (solo los que tienen al menos una). */
    private function localesMasReservados(): array
    {
        return Local::withCount('reservas')
            ->has('reservas')
            ->orderByDesc('reservas_count')
            ->take(5)
            ->get()
            ->map(fn (Local $l) => ['nombre' => $l->nombre, 'total' => (int) $l->reservas_count, 'color' => '#c2185b'])
            ->all();
    }
}
