{{--
    Panel del local: reservas recibidas. Ruta: GET /mi-local/reservas.
    Recibe: $local, $reservas (paginadas), $estado (filtro) y $pendientes.
    Los botones disponibles dependen del estado actual (flujo: pendiente → confirmada → entregada).
--}}
@extends('layouts.app')
@section('titulo', 'Reservas recibidas')

@section('contenido')
@include('panel._nav')
<section class="seccion panel-cuerpo">
  <div class="seccion-cabecera"><h2>Reservas de {{ $local->nombre }}</h2>
    <p>{{ $pendientes }} pendiente(s) por atender.</p></div>

  <div class="filtros-linea">
    <a class="chip {{ $estado === '' ? 'activa' : '' }}" href="{{ route('panel.reservas.index') }}">Todas</a>
    @foreach (\App\Models\Reserva::ESTADOS as $clave => $nombre)
      <a class="chip {{ $estado === $clave ? 'activa' : '' }}" href="{{ route('panel.reservas.index', ['estado' => $clave]) }}">{{ $nombre }}</a>
    @endforeach
  </div>

  <div class="tabla-envoltorio">
    <table class="tabla">
      <thead><tr><th>Código</th><th>Estudiante</th><th>Menú</th><th>Cant.</th><th>Total</th><th>Recojo</th><th>Estado</th><th>Acciones</th></tr></thead>
      <tbody>
        @forelse ($reservas as $reserva)
          <tr>
            <td class="codigo">{{ $reserva->codigo }}</td>
            <td>{{ $reserva->user->name }}@if ($reserva->nota)<span class="debajo">“{{ $reserva->nota }}”</span>@endif</td>
            <td>{{ $reserva->menu->nombre }}@if ($reserva->remate_id)<span class="debajo">Remate</span>@endif</td>
            <td>{{ $reserva->cantidad }}</td>
            <td class="text-nowrap">S/. {{ number_format($reserva->total, 2) }}</td>
            <td>{{ $reserva->fecha_recojo->format('d/m') }} · {{ substr($reserva->hora_recojo, 0, 5) }}</td>
            <td><span class="estado estado-{{ $reserva->estado }}">{{ $reserva->estado_nombre }}</span></td>
            <td class="acciones">
              @php($opciones = match ($reserva->estado) {
                  'pendiente' => ['confirmada' => ['Confirmar', 'btn-ok'], 'rechazada' => ['Rechazar', 'btn-borrar']],
                  'confirmada' => ['completada' => ['Entregada', 'btn-ok'], 'cancelada' => ['No vino', 'btn-borrar']],
                  default => [],
              })
              @foreach ($opciones as $nuevo => [$texto, $clase])
                <form method="POST" action="{{ route('panel.reservas.estado', $reserva) }}">
                  @csrf @method('PATCH')
                  <input type="hidden" name="estado" value="{{ $nuevo }}">
                  <button type="submit" class="btn {{ $clase }} btn-chico">{{ $texto }}</button>
                </form>
              @endforeach
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="tabla-vacia">No hay reservas para mostrar.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="paginacion">{{ $reservas->links() }}</div>
</section>
@endsection
