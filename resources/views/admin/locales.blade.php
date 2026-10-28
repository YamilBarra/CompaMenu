{{--
    Gestión de establecimientos. Ruta: GET /admin/locales. Recibe $locales (con puntaje y conteos).
--}}
@extends('layouts.app')
@section('titulo', 'Locales (admin)')

@section('contenido')
@include('admin._nav')
<section class="seccion panel-cuerpo">
  <div class="seccion-cabecera"><h2>Establecimientos</h2></div>
  <div class="tabla-envoltorio">
    <table class="tabla">
      <thead><tr><th>Local</th><th>Dueño</th><th>Menús</th><th>Remates</th><th>Puntaje</th><th>Estado</th><th>Acciones</th></tr></thead>
      <tbody>
        @forelse ($locales as $l)
          <tr>
            <td><a href="{{ route('locales.show', $l) }}"><strong>{{ $l->nombre }}</strong></a><span class="debajo">{{ $l->tipo_nombre }} · {{ $l->distancia_campus }} m</span></td>
            <td>{{ $l->user->name }}</td>
            <td>{{ $l->menus_count }}</td>
            <td>{{ $l->remates_count }}</td>
            <td>{{ $l->puntaje ? number_format($l->puntaje, 1) : '—' }}</td>
            <td><span class="estado {{ $l->activo ? 'estado-activo' : 'estado-inactivo' }}">{{ $l->activo ? 'Visible' : 'Oculto' }}</span></td>
            <td class="acciones">
              <form method="POST" action="{{ route('admin.locales.alternar', $l) }}">
                @csrf @method('PATCH')
                <button class="btn btn-editar btn-chico" type="submit">{{ $l->activo ? 'Ocultar' : 'Mostrar' }}</button>
              </form>
              <form method="POST" action="{{ route('admin.locales.destroy', $l) }}" data-confirmar="Se eliminará {{ $l->nombre }} con sus menús, remates y reservas.">
                @csrf @method('DELETE')
                <button class="btn btn-borrar btn-chico" type="submit">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="tabla-vacia">No hay locales registrados.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="paginacion">{{ $locales->links() }}</div>
</section>
@endsection
