{{--
    Supervisión de menús. Ruta: GET /admin/menus. Recibe $menus (paginados).
--}}
@extends('layouts.app')
@section('titulo', 'Menús (admin)')

@section('contenido')
@include('admin._nav')
<section class="seccion panel-cuerpo">
  <div class="seccion-cabecera"><h2>Menús publicados</h2></div>
  <div class="tabla-envoltorio">
    <table class="tabla">
      <thead><tr><th>Menú</th><th>Local</th><th>Precio</th><th>Porc.</th><th>Estado</th><th>Acciones</th></tr></thead>
      <tbody>
        @forelse ($menus as $m)
          <tr>
            <td><a href="{{ route('menus.show', $m) }}"><strong>{{ $m->nombre }}</strong></a><span class="debajo">{{ $m->tipo_nombre }}</span></td>
            <td>{{ $m->local->nombre }}</td>
            <td class="text-nowrap">S/. {{ number_format((float) $m->precio, 2) }}</td>
            <td>{{ $m->porciones }}</td>
            <td><span class="estado {{ $m->disponible ? 'estado-activo' : 'estado-inactivo' }}">{{ $m->disponible ? 'Disponible' : 'Agotado' }}</span></td>
            <td class="acciones">
              <form method="POST" action="{{ route('admin.menus.destroy', $m) }}" data-confirmar="Se eliminará «{{ $m->nombre }}».">
                @csrf @method('DELETE')
                <button class="btn btn-borrar btn-chico" type="submit">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="tabla-vacia">No hay menús publicados.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="paginacion">{{ $menus->links() }}</div>
</section>
@endsection
