<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Local;
use App\Models\Menu;
use App\Models\Remate;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Valoracion;

/** Panel administrativo: resumen con las métricas del documento (sección 6.6). */
class ResumenController extends Controller
{
    /**
     * Calcula las métricas del panel (sección 6.6 de la propuesta): estudiantes, establecimientos, menús, remates,
     * valoraciones, promedio de estrellas y reservas. El promedio solo cuenta valoraciones visibles.
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

        return view('admin.resumen', compact('metricas', 'ultimasReservas', 'ultimasResenas'));
    }
}
