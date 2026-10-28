{{--
    Todas las reservas (solo lectura). Ruta: GET /admin/reservas. Recibe $reservas (paginadas).
--}}
@extends('layouts.app')
@section('titulo', 'Reservas (admin)')

@section('contenido')
@include('admin._nav')
<section class="seccion panel-cuerpo">
  <div class="seccion-cabecera"><h2>Todas las reservas</h2></div>
  <div class="tabla-envoltorio">
    <table class="tabla">
      <thead><tr><th>Código</th><th>Estudiante</th><th>Local</th><th>Menú</th><th>Cant.</th><th>Total</th><th>Estado</th></tr></thead>
      <tbody>
        @forelse ($reservas as $r)
          <tr>
            <td class="codigo">{{ $r->codigo }}</td>
            <td>{{ $r->user->name }}</td>
            <td>{{ $r->local->nombre }}</td>
            <td>{{ $r->menu->nombre }}</td>
            <td>{{ $r->cantidad }}</td>
            <td class="text-nowrap">S/. {{ number_format($r->total, 2) }}</td>
            <td><span class="estado estado-{{ $r->estado }}">{{ $r->estado_nombre }}</span></td>
          </tr>
        @empty
          <tr><td colspan="7" class="tabla-vacia">Aún no hay reservas.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="paginacion">{{ $reservas->links() }}</div>
</section>
@endsection
