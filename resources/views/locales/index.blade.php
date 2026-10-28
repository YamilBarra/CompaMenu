{{--
    Lista de locales. Ruta: GET /locales (LocalController@index).
    Recibe: $locales (con puntaje y menús disponibles), $tipo y $q (filtros actuales).
--}}
@extends('layouts.app')
@section('titulo', 'Locales')

@section('contenido')
<section class="encabezado-pagina">
  <div class="encabezado-interior">
    <h1>Locales cerca del campus</h1>
    <p>Pensiones, quioscos y comedores que publican su menú en CompaMenu.</p>
  </div>
</section>

<section class="seccion">
  <div class="filtros-linea">
    <a class="chip {{ $tipo === '' ? 'activa' : '' }}" href="{{ route('locales.index') }}">Todos</a>
    @foreach (\App\Models\Local::TIPOS as $clave => $nombre)
      <a class="chip {{ $tipo === $clave ? 'activa' : '' }}" href="{{ route('locales.index', ['tipo' => $clave]) }}">{{ $nombre }}</a>
    @endforeach
    <form method="GET" action="{{ route('locales.index') }}" role="search">
      @if ($tipo !== '')<input type="hidden" name="tipo" value="{{ $tipo }}">@endif
      <input type="search" name="q" value="{{ $q }}" maxlength="60" placeholder="Buscar local" aria-label="Buscar local">
      <button class="btn btn-borde oscuro btn-chico" type="submit">Buscar</button>
    </form>
  </div>

  <div class="rejilla">
    @forelse ($locales as $local)
      <article class="local-tarjeta">
        <h3><a href="{{ route('locales.show', $local) }}">{{ $local->nombre }}</a></h3>
        <div class="etiquetas">
          <span class="etiqueta">{{ $local->tipo_nombre }}</span>
          <span class="etiqueta">{{ $local->distancia_campus }} m del campus</span>
        </div>
        <p class="valor"><x-estrellas :valor="$local->puntaje" :cantidad="$local->resenas_count" /></p>
        <p class="local">{{ $local->menus_disponibles }} menú(s) disponible(s)@if ($local->horario) · {{ $local->horario }}@endif</p>
        <div><a class="btn btn-principal btn-chico" href="{{ route('locales.show', $local) }}">Ver local</a></div>
      </article>
    @empty
      <p class="vacio">No encontramos locales con ese filtro.</p>
    @endforelse
  </div>
</section>
@endsection
