{{--
    Página de un local. Ruta: GET /locales/{local} (LocalController@show).
    Recibe: $local (con puntaje), $menus y $resenas.
--}}
@extends('layouts.app')
@section('titulo', $local->nombre)

@section('contenido')
<section class="encabezado-pagina">
  <div class="encabezado-interior">
    <h1>{{ $local->nombre }}</h1>
    <p>{{ $local->tipo_nombre }} · a {{ $local->distancia_campus }} m del campus @if ($local->direccion) · {{ $local->direccion }}@endif</p>
  </div>
</section>

<section class="seccion">
  <div class="dos-columnas" style="margin-top:0">
    <div class="caja">
      <h3>Información</h3>
      @if ($local->descripcion)<p>{{ $local->descripcion }}</p>@endif
      <dl class="datos-lista">
        <dt>Tipo</dt><dd>{{ $local->tipo_nombre }}</dd>
        @if ($local->direccion)<dt>Ubicación</dt><dd>{{ $local->direccion }}</dd>@endif
        @if ($local->horario)<dt>Horario</dt><dd>{{ $local->horario }}</dd>@endif
        @if ($local->telefono)<dt>Contacto</dt><dd>{{ $local->telefono }}</dd>@endif
        <dt>Puntaje</dt><dd><x-estrellas :valor="$local->puntaje" :cantidad="$local->resenas_count" /></dd>
      </dl>
    </div>
    <div class="caja">
      <h3>Reseñas recientes</h3>
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
        <p class="sin-resenas">Aún no hay reseñas de este local.</p>
      @endif
      <a class="btn btn-borde oscuro btn-chico mt-2" href="{{ route('resenas.index', ['local' => $local->id]) }}#formulario">Dejar una reseña</a>
    </div>
  </div>
</section>

<section class="seccion" style="padding-top:0">
  <div class="seccion-cabecera"><h2>Menús de {{ $local->nombre }}</h2></div>
  <div class="rejilla">
    @forelse ($menus as $menu)
      @include('menus._tarjeta', ['menu' => $menu])
    @empty
      <p class="vacio">Este local todavía no publicó menús.</p>
    @endforelse
  </div>
</section>
@endsection
