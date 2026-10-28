{{--
    Tarjeta de un menú (la usan Inicio, la página de un local y los resultados del buscador por fetch).
    Recibe: $menu con 'local' (con puntaje y total de reseñas) y 'remateActivo' ya cargados (eager loading,
    así no se hace una consulta extra por tarjeta).
    Muestra el botón Reservar solo si el menú tiene porciones y el usuario es invitado o estudiante.
--}}
@php
  $local = $menu->local;
  $remate = $menu->remateActivo;
  $reservable = $menu->disponible && $menu->porciones > 0;
  $puedeReservar = ! auth()->check() || auth()->user()->esEstudiante();
@endphp
<article @class(['tarjeta', 'agotada' => ! $reservable])>
  <div @class(['foto', 'sin-foto' => ! $menu->foto])>
    @if ($menu->foto)
      <img src="{{ $menu->foto_url }}" alt="{{ $menu->nombre }}" loading="lazy">
    @else
      Sin foto
    @endif
  </div>
  <div class="tarjeta-cuerpo">
    <div class="tarjeta-fila">
      <h3><a href="{{ route('menus.show', $menu) }}">{{ $menu->nombre }}</a></h3>
      <span class="precio">
        @if ($remate)
          <small>S/. {{ number_format((float) $menu->precio, 2) }}</small><span class="nuevo">S/. {{ number_format((float) $remate->precio_remate, 2) }}</span>
        @else
          S/. {{ number_format((float) $menu->precio, 2) }}
        @endif
      </span>
    </div>
    <p class="local"><a href="{{ route('locales.show', $local) }}">{{ $local->nombre }}</a></p>
    <div class="etiquetas">
      <span class="etiqueta">{{ $menu->tipo_nombre }}</span>
      <span class="etiqueta">{{ $local->distancia_campus }} m del campus</span>
      @if ($remate)<span class="etiqueta remate">Remate</span>@endif
      @if (! $menu->disponible)
        <span class="etiqueta agotado">Agotado</span>
      @elseif ($menu->porciones < 1)
        <span class="etiqueta agotado">Sin porciones</span>
      @endif
    </div>
    <p class="valor"><x-estrellas :valor="$local->puntaje" :cantidad="$local->resenas_count" /></p>
    <div class="tarjeta-acciones">
      <a class="btn btn-borde oscuro btn-chico" href="{{ route('menus.show', $menu) }}">Ver detalle</a>
      @if ($reservable && $puedeReservar)
        <a class="btn btn-principal btn-chico" href="{{ route('reservas.menu', $menu) }}">Reservar</a>
      @endif
    </div>
  </div>
</article>
