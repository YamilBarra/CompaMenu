{{--
    Panel del local: CRUD de menús. Rutas: GET /mi-local/menus y GET /mi-local/menus/{menu}/editar.
    Recibe: $local, $menus y $editar (el menú en edición o null para crear).
    Los platos se envían como platos[i][nombre|categoria] y se agregan o quitan con app.js (plantilla #plantillaPlato).
--}}
@extends('layouts.app')
@section('titulo', 'Mis menús')

@section('contenido')
@include('panel._nav')
@php
  $esEdicion = (bool) $editar;
  $platosForm = old('platos', $esEdicion
      ? $editar->platos->map(fn ($p) => ['nombre' => $p->nombre, 'categoria' => $p->categoria, 'descripcion' => $p->descripcion])->all()
      : [['nombre' => '', 'categoria' => 'segundo', 'descripcion' => '']]);
@endphp
<section class="seccion panel-cuerpo">
  <div class="seccion-cabecera"><h2>Menús de {{ $local->nombre }}</h2></div>
  <div class="panel-contenedor ancho">

    <!-- Formulario: crear o editar un menú -->
    <form class="form-panel" method="POST" enctype="multipart/form-data" novalidate id="formMenu"
          action="{{ $esEdicion ? route('panel.menus.update', $editar) : route('panel.menus.store') }}">
      @csrf
      @if ($esEdicion) @method('PUT') @endif
      <h3>{{ $esEdicion ? 'Editar menú' : 'Nuevo menú' }}</h3>

      <x-campo name="nombre" label="Nombre del menú" maxlength="80" :value="$editar?->nombre" placeholder="Ej: Ají de gallina con arroz" required />
      <div class="row g-2">
        <div class="col-6"><x-campo name="precio" label="Precio (S/.)" type="number" step="0.5" min="1" max="30" :value="$editar?->precio" required /></div>
        <div class="col-6"><x-selector name="tipo" label="Tipo" :opciones="\App\Models\Menu::TIPOS" :value="$editar?->tipo ?? 'criollo'" /></div>
      </div>
      <x-area name="descripcion" label="Descripción (opcional)" rows="2" maxlength="500" :value="$editar?->descripcion" />
      <div class="row g-2">
        <div class="col-6"><x-campo name="acompanamiento" label="Acompañamiento" maxlength="80" :value="$editar?->acompanamiento" placeholder="Arroz, papas..." /></div>
        <div class="col-6"><x-campo name="bebida" label="Bebida" maxlength="60" :value="$editar?->bebida" placeholder="Chicha, refresco..." /></div>
      </div>
      <div class="row g-2">
        <div class="col-4"><x-campo name="hora_inicio" label="Desde" type="time" :value="$editar?->hora_inicio ? substr($editar->hora_inicio, 0, 5) : null" /></div>
        <div class="col-4"><x-campo name="hora_fin" label="Hasta" type="time" :value="$editar?->hora_fin ? substr($editar->hora_fin, 0, 5) : null" /></div>
        <div class="col-4"><x-campo name="porciones" label="Porciones" type="number" min="0" max="200" :value="$editar?->porciones ?? 20" required /></div>
      </div>
      <x-campo name="fecha" label="Fecha del menú (opcional)" type="date" :value="$editar?->fecha?->format('Y-m-d')" />

      <!-- Platos que forman el menú -->
      <label>Platos del menú</label>
      <div id="platos">
        @foreach ($platosForm as $i => $plato)
          <div class="fila-plato">
            <select name="platos[{{ $i }}][categoria]" class="form-select" aria-label="Categoría">
              @foreach (\App\Models\Plato::CATEGORIAS as $clave => $nombre)
                <option value="{{ $clave }}" @selected(($plato['categoria'] ?? 'segundo') === $clave)>{{ $nombre }}</option>
              @endforeach
            </select>
            <input type="text" name="platos[{{ $i }}][nombre]" class="form-control" maxlength="80" placeholder="Nombre del plato" aria-label="Nombre del plato" value="{{ $plato['nombre'] ?? '' }}">
            <button type="button" class="btn btn-borrar btn-chico" data-quitar-plato aria-label="Quitar plato">✕</button>
          </div>
          @error("platos.$i.nombre")<p class="error-campo">{{ $message }}</p>@enderror
        @endforeach
      </div>
      <button type="button" class="btn btn-borde oscuro btn-chico mt-2" id="agregarPlato">+ Agregar plato</button>

      <label for="foto" class="mt-3">Foto (JPG, PNG o WEBP, máx. 2 MB)</label>
      <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" class="form-control @error('foto') invalido @enderror">
      @error('foto')<p class="error-campo">{{ $message }}</p>@enderror
      <img id="vistaPrevia" class="miniatura" alt="Vista previa de la foto" @if (! $editar?->foto) hidden @else src="{{ $editar->foto_url }}" @endif>
      @if ($esEdicion && $editar->foto)
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="quitar_foto" name="quitar_foto" value="1">
          <label class="form-check-label" for="quitar_foto">Quitar la foto actual</label>
        </div>
      @endif

      <input type="hidden" name="disponible" value="0">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" id="disponible" name="disponible" value="1" @checked(old('disponible', $editar?->disponible ?? true))>
        <label class="form-check-label" for="disponible">Disponible hoy</label>
      </div>

      <div class="acciones mt-3">
        <button type="submit" class="btn btn-principal">{{ $esEdicion ? 'Guardar cambios' : 'Agregar menú' }}</button>
        @if ($esEdicion)<a class="btn btn-borde oscuro" href="{{ route('panel.menus.index') }}">Cancelar</a>@endif
      </div>
    </form>

    <!-- Tabla con los menús del local -->
    <div class="tabla-envoltorio">
      <table class="tabla">
        <thead><tr><th>Menú</th><th>Precio</th><th>Porc.</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
          @forelse ($menus as $menu)
            <tr>
              <td><strong>{{ $menu->nombre }}</strong><span class="debajo">{{ $menu->tipo_nombre }} · {{ $menu->platos->count() }} plato(s)</span></td>
              <td class="text-nowrap">S/. {{ number_format((float) $menu->precio, 2) }}</td>
              <td>{{ $menu->porciones }}</td>
              <td>
                <form method="POST" action="{{ route('panel.menus.disponibilidad', $menu) }}">
                  @csrf @method('PATCH')
                  <button type="submit" class="estado {{ $menu->disponible ? 'estado-activo' : 'estado-inactivo' }}" style="border:0" title="Cambiar disponibilidad">{{ $menu->disponible ? 'Disponible' : 'Agotado' }}</button>
                </form>
              </td>
              <td class="acciones">
                <a class="btn btn-editar btn-chico" href="{{ route('panel.menus.edit', $menu) }}#formMenu">Editar</a>
                <form method="POST" action="{{ route('panel.menus.destroy', $menu) }}" data-confirmar="Se eliminará «{{ $menu->nombre }}» y no se puede deshacer.">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-borrar btn-chico">Eliminar</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="tabla-vacia">Aún no publicaste menús. Usa el formulario para agregar el primero.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</section>

<template id="plantillaPlato">
  <div class="fila-plato">
    <select name="platos[__I__][categoria]" class="form-select" aria-label="Categoría">
      @foreach (\App\Models\Plato::CATEGORIAS as $clave => $nombre)<option value="{{ $clave }}">{{ $nombre }}</option>@endforeach
    </select>
    <input type="text" name="platos[__I__][nombre]" class="form-control" maxlength="80" placeholder="Nombre del plato" aria-label="Nombre del plato">
    <button type="button" class="btn btn-borrar btn-chico" data-quitar-plato aria-label="Quitar plato">✕</button>
  </div>
</template>
@endsection
