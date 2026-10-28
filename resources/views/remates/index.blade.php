{{--
    Remate Solidario público. Ruta: GET /remates (RemateController@index).
    Recibe: $remates vigentes con 'menu' y 'local'.
--}}
@extends('layouts.app')
@section('titulo', 'Remate solidario')
@section('descripcion', 'Platos rebajados por la tarde cerca de tu campus')

@section('contenido')
<section class="encabezado-pagina">
  <div class="encabezado-interior">
    <h1>Remate solidario</h1>
    <p>Platos rebajados por la tarde. Reserva tu porción antes de que se acaben.</p>
  </div>
</section>

<section class="seccion">
  @php($puedeReservar = ! auth()->check() || auth()->user()->esEstudiante())
  <div class="rejilla" id="remates">
    @forelse ($remates as $remate)
      @php($menu = $remate->menu)
      <article class="tarjeta">
        <div @class(['foto', 'sin-foto' => ! $menu->foto])>
          @if ($menu->foto)<img src="{{ $menu->foto_url }}" alt="{{ $menu->nombre }}" loading="lazy">@else Sin foto @endif
        </div>
        <div class="tarjeta-cuerpo">
          <div class="tarjeta-fila">
            <h3><a href="{{ route('menus.show', $menu) }}">{{ $menu->nombre }}</a></h3>
            <span class="precio"><small>S/. {{ number_format((float) $menu->precio, 2) }}</small><b>S/. {{ number_format((float) $remate->precio_remate, 2) }}</b></span>
          </div>
          <p class="local"><a href="{{ route('locales.show', $remate->local) }}">{{ $remate->local->nombre }}</a></p>
          <div class="etiquetas">
            <span class="etiqueta alerta">Quedan {{ $remate->porciones }} porciones</span>
            <span class="etiqueta hora">{{ $remate->hora_texto }}</span>
          </div>
          @if ($puedeReservar)
            <div class="tarjeta-acciones">
              <a class="btn btn-principal btn-chico" href="{{ route('reservas.remate', $remate) }}">Reservar porción</a>
            </div>
          @endif
        </div>
      </article>
    @empty
      <p class="vacio">Por ahora no hay remates activos. Vuelve por la tarde.</p>
    @endforelse
  </div>
</section>
@endsection
