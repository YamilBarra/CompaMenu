<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Remate;
use App\Models\Reserva;
use App\Support\Texto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Reservas de platos (lado del estudiante): apartar porciones, ver y cancelar sus reservas. */
class ReservaController extends Controller
{
    /**
     * Reservas del estudiante autenticado, de la más nueva a la más antigua.
     * Ruta: GET /mis-reservas.
     */
    public function mis(Request $request)
    {
        $reservas = $request->user()->reservas()
            ->with(['menu', 'local', 'remate'])
            ->latest()
            ->paginate(10);

        return view('reservas.mis', compact('reservas'));
    }

    /**
     * Formulario para reservar un menú. Si ya no se puede reservar (agotado, sin porciones o local oculto)
     * vuelve al detalle del menú con un mensaje.
     * Ruta: GET /reservar/menu/{menu}.
     */
    public function crearMenu(Menu $menu)
    {
        $menu->load(['local' => fn ($q) => $q->conPuntaje()]);

        if ($error = $this->noReservable($menu->disponible && $menu->local->activo, $menu->porciones)) {
            return redirect()->route('menus.show', $menu)->with('error', $error);
        }

        return view('reservas.create', [
            'menu' => $menu,
            'remate' => null,
            'local' => $menu->local,
            'precio' => $menu->precio,
            'stock' => $menu->porciones,
            'ini' => $menu->hora_inicio ? substr($menu->hora_inicio, 0, 5) : null,
            'fin' => $menu->hora_fin ? substr($menu->hora_fin, 0, 5) : null,
        ]);
    }

    /**
     * Igual que crearMenu pero a precio de remate: el stock que cuenta es el de las porciones del remate.
     * Ruta: GET /reservar/remate/{remate}.
     */
    public function crearRemate(Remate $remate)
    {
        $remate->load(['menu', 'local']);

        $vigente = $remate->activo && $remate->menu->disponible && $remate->local->activo;
        if ($error = $this->noReservable($vigente, $remate->porciones)) {
            return redirect()->route('remates.index')->with('error', $error);
        }

        return view('reservas.create', [
            'menu' => $remate->menu,
            'remate' => $remate,
            'local' => $remate->local,
            'precio' => $remate->precio_remate,
            'stock' => $remate->porciones,
            'ini' => substr($remate->hora_inicio, 0, 5),
            'fin' => $remate->hora_fin ? substr($remate->hora_fin, 0, 5) : null,
        ]);
    }

    /**
     * Crea la reserva.
     *
     * Todo ocurre dentro de DB::transaction con lockForUpdate(): si dos estudiantes piden la última porción
     * al mismo tiempo, solo uno la consigue (no se vende de más). Se valida el stock, que la hora de recojo
     * esté dentro del horario del menú o remate, y se descuentan las porciones. El precio se guarda en la
     * reserva (precio_unitario) para que un cambio posterior del menú no altere lo acordado.
     *
     * @see https://laravel.com/docs/queries#pessimistic-locking
     * @see https://laravel.com/docs/database#database-transactions
     * Ruta: POST /reservas · Rol: estudiante.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
            'remate_id' => ['nullable', 'integer', 'exists:remates,id'],
            'cantidad' => ['required', 'integer', 'between:1,5'],
            'hora_recojo' => ['required', 'date_format:H:i'],
            'nota' => ['nullable', 'string', 'max:120'],
        ]);

        // Todo en una transacción: si dos estudiantes piden la última porción, solo uno la consigue
        $reserva = DB::transaction(function () use ($request, $datos) {
            $menu = Menu::with('local')->lockForUpdate()->findOrFail($datos['menu_id']);
            $remate = null;

            if (! empty($datos['remate_id'])) {
                $remate = Remate::lockForUpdate()->where('menu_id', $menu->id)->find($datos['remate_id']);
                if (! $remate || ! $remate->activo) {
                    throw ValidationException::withMessages(['cantidad' => 'Ese remate ya no está disponible.']);
                }
            }

            if (! $menu->disponible || ! $menu->local->activo) {
                throw ValidationException::withMessages(['cantidad' => 'Este menú ya no está disponible.']);
            }

            $stock = $remate ? $remate->porciones : $menu->porciones;
            if ($stock < 1) {
                throw ValidationException::withMessages(['cantidad' => 'Ya no quedan porciones.']);
            }
            if ($datos['cantidad'] > $stock) {
                throw ValidationException::withMessages(['cantidad' => "Solo quedan {$stock} porción(es)."]);
            }

            // La hora de recojo debe estar dentro del horario del menú o del remate
            $inicio = $remate ? $remate->hora_inicio : $menu->hora_inicio;
            $fin = $remate ? $remate->hora_fin : $menu->hora_fin;
            $inicio = $inicio ? substr($inicio, 0, 5) : null;
            $fin = $fin ? substr($fin, 0, 5) : null;
            if (($inicio && $datos['hora_recojo'] < $inicio) || ($fin && $datos['hora_recojo'] > $fin)) {
                $rango = $fin ? "entre {$inicio} y {$fin}" : "desde las {$inicio}";
                throw ValidationException::withMessages(['hora_recojo' => "Elige una hora de recojo {$rango}."]);
            }

            // Apartamos las porciones
            if ($remate) {
                $remate->decrement('porciones', $datos['cantidad']);
            } else {
                $menu->decrement('porciones', $datos['cantidad']);
            }

            return Reserva::create([
                'codigo' => Reserva::nuevoCodigo(),
                'user_id' => $request->user()->id,
                'local_id' => $menu->local_id,
                'menu_id' => $menu->id,
                'remate_id' => $remate?->id,
                'cantidad' => $datos['cantidad'],
                'precio_unitario' => $remate ? $remate->precio_remate : $menu->precio,
                'fecha_recojo' => now()->toDateString(),
                'hora_recojo' => $datos['hora_recojo'],
                'nota' => Texto::limpio($datos['nota'] ?? null),
                'estado' => 'pendiente',
            ]);
        });

        return redirect()->route('reservas.mis')
            ->with('exito', "Reservaste tus porciones. Tu código es {$reserva->codigo}: muéstralo en el local.");
    }

    /**
     * El estudiante cancela su reserva mientras esté viva (pendiente o confirmada) y se devuelven las porciones.
     * Solo puede cancelar las suyas (403 si es de otro).
     * Ruta: PATCH /reservas/{reserva}/cancelar.
     */
    public function cancelar(Request $request, Reserva $reserva)
    {
        abort_unless($reserva->user_id === $request->user()->id, 403);

        $cancelada = DB::transaction(function () use ($reserva) {
            $actual = Reserva::lockForUpdate()->find($reserva->id);
            if (! $actual || ! $actual->estaViva()) {
                return false;
            }
            $actual->liberarPorciones();
            $actual->update(['estado' => 'cancelada']);

            return true;
        });

        return back()->with($cancelada ? 'exito' : 'error', $cancelada ? 'Cancelaste tu reserva.' : 'Esa reserva ya no se puede cancelar.');
    }

    /**
     * Devuelve el mensaje de error si el menú no se puede reservar, o null si sí se puede.
     */
    private function noReservable(bool $vigente, int $porciones): ?string
    {
        if (! $vigente) {
            return 'Este menú ya no está disponible.';
        }
        if ($porciones < 1) {
            return 'Ya no quedan porciones para reservar.';
        }

        return null;
    }
}
