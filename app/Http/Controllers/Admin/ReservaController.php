<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reserva;

/** El administrador puede ver todas las reservas del sistema. */
class ReservaController extends Controller
{
    /**
     * Lista de solo lectura de todas las reservas del sistema. El administrador no las modifica:
     * cada local atiende las suyas.
     * Ruta: GET /admin/reservas.
     */
    public function index()
    {
        $reservas = Reserva::with(['user', 'menu', 'local'])->latest()->paginate(12);

        return view('admin.reservas', compact('reservas'));
    }
}
