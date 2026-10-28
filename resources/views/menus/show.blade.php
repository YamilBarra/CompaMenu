{{--
    Detalle de un menú. Ruta: GET /menus/{menu} (MenuController@show).
    Recibe: $menu (con local, platos y remate activo) y $resenas (últimas 3 del local).
--}}
@extends('layouts.app')
@section('titulo', $menu->nombre)

@section('contenido')
@php
  $local = $menu->local;
  $remate = $menu->remateActivo;
  $reservable = $menu->disponible && $menu->porciones > 0;
  $puedeReservar = ! auth()->check() || auth()->user()->esEstudiante();
@endphp
<section class="encabezado-pagina">
  <div class="encabezado-interior">
    <h1>{{ $menu->nombre }}</h1>
    <p>{{ $local->nombre }} · {{ $local->tipo_nombre }} · a {{ $local->distancia_campus }} m del campus</p>
  </div>
</section>

<section class="seccion">
  <div class="detalle">
    <div class="detalle-foto">
      <div @class(['foto', 'sin-foto' => ! $menu->foto])>
        @if ($menu->foto)
          <img src="{{ $menu->foto_url }}" alt="{{ $menu->nombre }}">
        @else
          Sin foto
        @endif
      </div>
    </div>

    <div class="detalle-info">
      <h2>{{ $menu->nombre }}</h2>
      <p class="detalle-precio">
        @if ($remate)
          <small>S/. {{ number_format((float) $menu->precio, 2) }}</small>S/. {{ number_format((float) $remate->precio_remate, 2) }}
        @else
          S/. {{ number_format((float) $menu->precio, 2) }}
        @endif
      </p>
      <div class="etiquetas">
        <span class="etiqueta">{{ $menu->tipo_nombre }}</span>
        @if ($remate)<span class="etiqueta remate">Remate: {{ $remate->hora_texto }}</span>@endif
        @if (! $menu->disponible)
          <span class="etiqueta agotado">Agotado</span>
        @elseif ($menu->porciones < 1)
          <span class="etiqueta agotado">Sin porciones</span>
        @else
          <span class="etiqueta">Quedan {{ $menu->porciones }} porciones</span>
        @endif
      </div>
      <p class="mt-2"><x-estrellas :valor="$local->puntaje" :cantidad="$local->resenas_count" /></p>

      @if ($menu->descripcion)
        <p class="mt-3">{{ $menu->descripcion }}</p>
      @endif

      @if ($menu->platos->isNotEmpty())
        <h3 class="mt-3">Qué incluye</h3>
        <ul class="lista-platos">
          @foreach ($menu->platos as $plato)
            <li>
              <span class="cat">{{ $plato->categoria_nombre }}</span>
              <span>{{ $plato->nombre }}@if ($plato->descripcion)<span class="debajo ayuda">{{ $plato->descripcion }}</span>@endif</span>
            </li>
          @endforeach
        </ul>
      @endif

      <dl class="datos-lista">
        @if ($menu->acompanamiento)<dt>Acompañamiento</dt><dd>{{ $menu->acompanamiento }}</dd>@endif
        @if ($menu->bebida)<dt>Bebida</dt><dd>{{ $menu->bebida }}</dd>@endif
        @if ($menu->horario)<dt>Horario</dt><dd>{{ $menu->horario }}</dd>@endif
        @if ($menu->fecha)<dt>Fecha</dt><dd>{{ $menu->fecha->translatedFormat('l j \d\e F') }}</dd>@endif
      </dl>

      <div class="tarjeta-acciones">
        @if ($reservable && $puedeReservar)
          <a class="btn btn-principal" href="{{ $remate ? route('reservas.remate', $remate) : route('reservas.menu', $menu) }}">Reservar {{ $remate ? 'a precio de remate' : 'este menú' }}</a>
        @endif
        <a class="btn btn-borde oscuro" href="{{ route('inicio') }}">Volver a los menús</a>
      </div>
    </div>
  </div>
</section>

<section class="seccion franja">
  <div class="dos-columnas" style="margin-top:0">
    <div class="caja">
      <h3>{{ $local->nombre }}</h3>
      <dl class="datos-lista">
        <dt>Tipo</dt><dd>{{ $local->tipo_nombre }}</dd>
        @if ($local->direccion)<dt>Dirección</dt><dd>{{ $local->direccion }}</dd>@endif
        <dt>Distancia</dt><dd>{{ $local->distancia_campus }} m del campus</dd>
        @if ($local->horario)<dt>Horario</dt><dd>{{ $local->horario }}</dd>@endif
        @if ($local->telefono)<dt>Teléfono</dt><dd>{{ $local->telefono }}</dd>@endif
      </dl>
      <a class="btn btn-borde oscuro btn-chico" href="{{ route('locales.show', $local) }}">Ver todos sus menús</a>
    </div>
    <div class="caja">
      <h3>Lo que dicen los estudiantes</h3>
      @if ($resenas->isNotEmpty())
        <ul class="mini-lista">
          @foreach ($resenas as $resena)
            <li>
              <span class="estrellas">{{ str_repeat('★', $resena->puntaje) }}{{ str_repeat('☆', 5 - $resena->puntaje) }}</span>
              <strong>{{ $resena->user->name }}</strong><br>{{ $resena->comentario }}
            </li>
          @endforeach
        </ul>
      @else
        <p class="sin-resenas">Este local aún no tiene reseñas.</p>
      @endif
      <a class="btn btn-borde oscuro btn-chico mt-2" href="{{ route('resenas.index', ['local' => $local->id]) }}#formulario">Dejar una reseña</a>
    </div>
  </div>
</section>
@endsection
