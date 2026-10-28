{{--
    Reservas del estudiante. Ruta: GET /mis-reservas (ReservaController@mis). Recibe $reservas (paginadas).
    El botón Cancelar solo aparece mientras la reserva está viva (pendiente o confirmada).
--}}
@extends('layouts.app')
@section('titulo', 'Mis reservas')

@section('contenido')
<section class="encabezado-pagina">
  <div class="encabezado-interior">
    <h1>Mis reservas</h1>
    <p>Muestra tu código en el local al recoger tu pedido.</p>
  </div>
</section>

<section class="seccion">
  <div class="tabla-envoltorio">
    <table class="tabla">
      <thead>
        <tr><th>Código</th><th>Menú</th><th>Cant.</th><th>Total</th><th>Recojo</th><th>Estado</th><th></th></tr>
      </thead>
      <tbody>
        @forelse ($reservas as $reserva)
          <tr>
            <td class="codigo">{{ $reserva->codigo }}</td>
            <td><a href="{{ route('menus.show', $reserva->menu) }}">{{ $reserva->menu->nombre }}</a>
              <span class="debajo">{{ $reserva->local->nombre }}@if ($reserva->remate_id) · remate @endif</span></td>
            <td>{{ $reserva->cantidad }}</td>
            <td class="text-nowrap">S/. {{ number_format($reserva->total, 2) }}</td>
            <td>{{ $reserva->fecha_recojo->format('d/m') }} · {{ substr($reserva->hora_recojo, 0, 5) }}</td>
            <td><span class="estado estado-{{ $reserva->estado }}">{{ $reserva->estado_nombre }}</span></td>
            <td>
              @if ($reserva->estaViva())
                <form method="POST" action="{{ route('reservas.cancelar', $reserva) }}" data-confirmar="Se cancelará tu reserva {{ $reserva->codigo }} y las porciones quedarán libres.">
                  @csrf @method('PATCH')
                  <button class="btn btn-borrar btn-chico" type="submit">Cancelar</button>
                </form>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="tabla-vacia">Todavía no tienes reservas. <a href="{{ route('inicio') }}"><b>Busca un menú</b></a> y resérvalo.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="paginacion">{{ $reservas->links() }}</div>
</section>
@endsection
