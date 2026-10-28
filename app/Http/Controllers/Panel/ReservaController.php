<?php

namespace App\Http\Controllers\Panel;

use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Reservas que recibe el local: confirmar, rechazar, marcar como entregada. */
class ReservaController extends PanelController
{
    /** Cambios de estado permitidos. */
    private const TRANSICIONES = [
        'pendiente' => ['confirmada', 'rechazada'],
        'confirmada' => ['completada', 'cancelada'],
    ];

    /**
     * Reservas recibidas por el local, con filtro por estado. Las pendientes salen primero
     * (se usa CASE y no FIELD() para que funcione igual en MySQL y en SQLite).
     * Ruta: GET /mi-local/reservas.
     */
    public function index(Request $request)
    {
        $local = $this->local($request);
        $estado = (string) $request->query('estado', '');

        $reservas = $local->reservas()
            ->with(['user', 'menu', 'remate'])
            ->when(array_key_exists($estado, Reserva::ESTADOS), fn ($q) => $q->where('estado', $estado))
            ->orderByRaw("CASE estado WHEN 'pendiente' THEN 1 WHEN 'confirmada' THEN 2 WHEN 'completada' THEN 3 WHEN 'cancelada' THEN 4 ELSE 5 END")
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $pendientes = $local->reservas()->where('estado', 'pendiente')->count();

        return view('panel.reservas', compact('local', 'reservas', 'estado', 'pendientes'));
    }

    /**
     * Cambia el estado de una reserva siguiendo TRANSICIONES (no se pueden saltar pasos).
     * Al rechazar o cancelar se devuelven las porciones. Se hace en una transacción con bloqueo de fila
     * para evitar cambios simultáneos sobre la misma reserva.
     * Ruta: PATCH /mi-local/reservas/{reserva}/estado.
     */
    public function estado(Request $request, Reserva $reserva)
    {
        $local = $this->local($request);
        abort_unless($reserva->local_id === $local->id, 404);

        $nuevo = $request->validate([
            'estado' => ['required', Rule::in(['confirmada', 'rechazada', 'completada', 'cancelada'])],
        ])['estado'];

        $resultado = DB::transaction(function () use ($reserva, $nuevo) {
            $actual = Reserva::lockForUpdate()->find($reserva->id);

            if (! in_array($nuevo, self::TRANSICIONES[$actual->estado] ?? [], true)) {
                return false;
            }
            // Si la reserva se rechaza o se cancela, las porciones vuelven a estar disponibles
            if (in_array($nuevo, ['rechazada', 'cancelada'], true)) {
                $actual->liberarPorciones();
            }
            $actual->update(['estado' => $nuevo]);

            return true;
        });

        return back()->with($resultado ? 'exito' : 'error', $resultado ? 'Actualizamos la reserva.' : 'Ese cambio de estado no es posible.');
    }
}
