{{--
    Moderación de valoraciones. Ruta: GET /admin/valoraciones. Recibe $valoraciones (paginadas).
--}}
@extends('layouts.app')
@section('titulo', 'Valoraciones (admin)')

@section('contenido')
@include('admin._nav')
<section class="seccion panel-cuerpo">
  <div class="seccion-cabecera"><h2>Moderación de valoraciones</h2>
    <p>Oculta o elimina las reseñas que incumplan las normas de respeto.</p></div>
  <div class="tabla-envoltorio">
    <table class="tabla">
      <thead><tr><th>Local</th><th>Estudiante</th><th>Puntaje</th><th>Comentario</th><th>Estado</th><th>Acciones</th></tr></thead>
      <tbody>
        @forelse ($valoraciones as $v)
          <tr>
            <td>{{ $v->local->nombre }}</td>
            <td>{{ $v->user->name }}</td>
            <td><span class="estrellas">{{ str_repeat('★', $v->puntaje) }}{{ str_repeat('☆', 5 - $v->puntaje) }}</span></td>
            <td>{{ $v->comentario }}</td>
            <td><span class="estado {{ $v->visible ? 'estado-visible' : 'estado-oculta' }}">{{ $v->visible ? 'Visible' : 'Oculta' }}</span></td>
            <td class="acciones">
              <form method="POST" action="{{ route('admin.valoraciones.alternar', $v) }}">
                @csrf @method('PATCH')
                <button class="btn btn-editar btn-chico" type="submit">{{ $v->visible ? 'Ocultar' : 'Mostrar' }}</button>
              </form>
              <form method="POST" action="{{ route('admin.valoraciones.destroy', $v) }}" data-confirmar="Se eliminará esta valoración.">
                @csrf @method('DELETE')
                <button class="btn btn-borrar btn-chico" type="submit">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="tabla-vacia">Aún no hay valoraciones.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="paginacion">{{ $valoraciones->links() }}</div>
</section>
@endsection
