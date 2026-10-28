{{--
    Panel del local: CRUD de remates. Rutas: GET /mi-local/remates y GET /mi-local/remates/{remate}/editar.
    Recibe: $local, $remates, $menus (los del propio local) y $editar.
--}}
@extends('layouts.app')
@section('titulo', 'Mis remates')

@section('contenido')
@include('panel._nav')
@php($esEdicion = (bool) $editar)
<section class="seccion panel-cuerpo">
  <div class="seccion-cabecera"><h2>Remate solidario de {{ $local->nombre }}</h2>
    <p>Rebaja un menú en las últimas horas para que no se desperdicie.</p></div>
  <div class="panel-contenedor">
    <form class="form-panel" method="POST" novalidate id="formRemate"
          action="{{ $esEdicion ? route('panel.remates.update', $editar) : route('panel.remates.store') }}">
      @csrf
      @if ($esEdicion) @method('PUT') @endif
      <h3>{{ $esEdicion ? 'Editar remate' : 'Nuevo remate' }}</h3>

      @if ($menus->isEmpty())
        <p class="alert alert-warning">Primero crea un menú para poder rematarlo.</p>
      @else
        <x-selector name="menu_id" label="Menú a rematar" :opciones="$menus->mapWithKeys(fn ($m) => [$m->id => $m->nombre.' (S/. '.number_format((float) $m->precio, 2).')'])" :value="$editar?->menu_id" vacio="Elige un menú" />
        <div class="row g-2">
          <div class="col-6"><x-campo name="precio_remate" label="Precio de remate (S/.)" type="number" step="0.5" min="0.5" max="30" :value="$editar?->precio_remate" required /></div>
          <div class="col-6"><x-campo name="porciones" label="Porciones" type="number" min="1" max="200" :value="$editar?->porciones ?? 5" required /></div>
        </div>
        <div class="row g-2">
          <div class="col-6"><x-campo name="hora_inicio" label="Desde las" type="time" :value="$editar ? substr($editar->hora_inicio, 0, 5) : '15:00'" required /></div>
          <div class="col-6"><x-campo name="hora_fin" label="Hasta las (opcional)" type="time" :value="$editar?->hora_fin ? substr($editar->hora_fin, 0, 5) : null" /></div>
        </div>
        <input type="hidden" name="activo" value="0">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" @checked(old('activo', $editar?->activo ?? true))>
          <label class="form-check-label" for="activo">Remate activo (visible para los estudiantes)</label>
        </div>
        <div class="acciones mt-3">
          <button type="submit" class="btn btn-principal">{{ $esEdicion ? 'Guardar cambios' : 'Publicar remate' }}</button>
          @if ($esEdicion)<a class="btn btn-borde oscuro" href="{{ route('panel.remates.index') }}">Cancelar</a>@endif
        </div>
      @endif
    </form>

    <div class="tabla-envoltorio">
      <table class="tabla">
        <thead><tr><th>Menú</th><th>Remate</th><th>Porc.</th><th>Horario</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
          @forelse ($remates as $remate)
            <tr>
              <td><strong>{{ $remate->menu->nombre }}</strong><span class="debajo">Normal: S/. {{ number_format((float) $remate->menu->precio, 2) }}</span></td>
              <td class="text-nowrap">S/. {{ number_format((float) $remate->precio_remate, 2) }}</td>
              <td>{{ $remate->porciones }}</td>
              <td>{{ substr($remate->hora_inicio, 0, 5) }}@if ($remate->hora_fin) – {{ substr($remate->hora_fin, 0, 5) }}@endif</td>
              <td><span class="estado {{ $remate->activo && $remate->porciones > 0 ? 'estado-activo' : 'estado-inactivo' }}">{{ $remate->porciones < 1 ? 'Agotado' : ($remate->activo ? 'Activo' : 'Pausado') }}</span></td>
              <td class="acciones">
                <a class="btn btn-editar btn-chico" href="{{ route('panel.remates.edit', $remate) }}#formRemate">Editar</a>
                <form method="POST" action="{{ route('panel.remates.destroy', $remate) }}" data-confirmar="Se retirará este remate.">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-borrar btn-chico">Retirar</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="tabla-vacia">No tienes remates publicados.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</section>
@endsection
