{{--
    Panel del local: datos del negocio. Ruta: GET /mi-local/datos. Recibe $local (puede ser null).
--}}
@extends('layouts.app')
@section('titulo', 'Datos del local')

@section('contenido')
@include('panel._nav')
<section class="seccion panel-cuerpo">
  <div class="seccion-cabecera"><h2>Datos de tu local</h2>
    <p>Esta información se muestra en la página de tu local.</p></div>
  <form class="form-panel" style="max-width:640px" method="POST" action="{{ route('panel.local.update') }}" novalidate>
    @csrf @method('PUT')
    <x-campo name="nombre" label="Nombre del local" maxlength="60" :value="$local?->nombre" required />
    <div class="row g-2">
      <div class="col-md-6"><x-selector name="tipo_local" label="Tipo de local" :opciones="\App\Models\Local::TIPOS" :value="$local?->tipo_local ?? 'comedor'" /></div>
      <div class="col-md-6"><x-campo name="distancia_campus" label="Distancia al campus (m)" type="number" min="0" max="5000" step="10" :value="$local?->distancia_campus" required /></div>
    </div>
    <x-campo name="direccion" label="Dirección" maxlength="120" :value="$local?->direccion" />
    <div class="row g-2">
      <div class="col-md-6"><x-campo name="horario" label="Horario de atención" maxlength="80" placeholder="Lun a Sáb 11:30 – 15:30" :value="$local?->horario" /></div>
      <div class="col-md-6"><x-campo name="telefono" label="Teléfono" type="tel" :value="$local?->telefono" /></div>
    </div>
    <x-area name="descripcion" label="Descripción" rows="3" maxlength="500" :value="$local?->descripcion" />
    <button type="submit" class="btn btn-principal mt-3">Guardar datos</button>
  </form>
</section>
@endsection
