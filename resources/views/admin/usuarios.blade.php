{{--
    Gestión de usuarios. Ruta: GET /admin/usuarios. Recibe $usuarios (paginados), $rol y $q (filtros).
--}}
@extends('layouts.app')
@section('titulo', 'Usuarios')

@section('contenido')
@include('admin._nav')
<section class="seccion panel-cuerpo">
  <div class="seccion-cabecera"><h2>Usuarios</h2></div>
  <div class="herramientas">
    <form method="GET" action="{{ route('admin.usuarios.index') }}" role="search">
      <select name="rol" class="form-select" aria-label="Filtrar por rol">
        <option value="">Todos los roles</option>
        <option value="estudiante" @selected($rol === 'estudiante')>Estudiantes</option>
        <option value="establecimiento" @selected($rol === 'establecimiento')>Establecimientos</option>
        <option value="administrador" @selected($rol === 'administrador')>Administradores</option>
      </select>
      <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Nombre o correo" aria-label="Buscar usuario">
      <button class="btn btn-principal btn-chico" type="submit">Filtrar</button>
    </form>
  </div>
  <div class="tabla-envoltorio">
    <table class="tabla">
      <thead><tr><th>Usuario</th><th>Rol</th><th>Local</th><th>Estado</th><th>Acciones</th></tr></thead>
      <tbody>
        @forelse ($usuarios as $u)
          <tr>
            <td><strong>{{ $u->name }}</strong><span class="debajo">{{ $u->email }}</span></td>
            <td>{{ $u->rol_nombre }}</td>
            <td>{{ $u->local?->nombre ?? '—' }}</td>
            <td><span class="estado {{ $u->activo ? 'estado-activo' : 'estado-inactivo' }}">{{ $u->activo ? 'Activo' : 'Desactivado' }}</span></td>
            <td class="acciones">
              @if ($u->id !== auth()->id())
                <form method="POST" action="{{ route('admin.usuarios.alternar', $u) }}">
                  @csrf @method('PATCH')
                  <button class="btn btn-editar btn-chico" type="submit">{{ $u->activo ? 'Desactivar' : 'Activar' }}</button>
                </form>
                <form method="POST" action="{{ route('admin.usuarios.destroy', $u) }}" data-confirmar="Se eliminará a {{ $u->name }} y todo lo que publicó.">
                  @csrf @method('DELETE')
                  <button class="btn btn-borrar btn-chico" type="submit">Eliminar</button>
                </form>
              @else
                <span class="ayuda">Tu cuenta</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="tabla-vacia">No hay usuarios con ese filtro.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="paginacion">{{ $usuarios->links() }}</div>
</section>
@endsection
