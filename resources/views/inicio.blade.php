{{--
    Página de inicio con portada y buscador. Ruta: GET / (HomeController@index).
    Recibe: $menus, $filtros (valores actuales de cada filtro), $totalLocales y $totalRemates.
    El formulario funciona con GET sin JavaScript; public/js/app.js lo mejora con fetch() sin recargar.
--}}
@extends('layouts.app')
@section('titulo', 'Inicio')

@section('contenido')
<!-- Portada con el buscador de menús -->
<section class="portada">
  <div class="portada-interior">
    <div class="portada-texto">
      <h1>Almuerza bien sin pasarte de tu presupuesto</h1>
      <p>Encuentra el menú del día en las pensiones y comedores cercanos a tu campus, con precio, distancia y opiniones de otros estudiantes.</p>
      <div class="portada-datos">
        <div><strong>S/. 6 – 8</strong><span>menús del día</span></div>
        <div><strong id="contadorLocales">{{ $totalLocales }}</strong><span>locales registrados</span></div>
        <div><strong id="contadorRemates">{{ $totalRemates }}</strong><span>remates ahora</span></div>
      </div>
      <div class="portada-botones">
        <a class="btn btn-principal" href="{{ route('remates.index') }}">Ver remates de hoy</a>
        <a class="btn btn-borde" href="{{ route('resenas.index') }}">Leer reseñas</a>
      </div>
    </div>

    <!-- Filtros: se envían con fetch (JavaScript) y, si no hay JavaScript, con el botón Buscar -->
    <form class="buscador" id="buscador" method="GET" action="{{ route('inicio') }}" aria-label="Buscador de menús">
      <h2>¿Cuánto tienes para hoy?</h2>
      <label for="precio">Precio máximo: <output id="precioValor">S/. {{ number_format($filtros['precio'], 2) }}</output></label>
      <input type="range" id="precio" name="precio" min="4" max="12" step="0.5" value="{{ min(12, max(4, $filtros['precio'])) }}">
      <label for="tipo">Tipo de plato</label>
      <select id="tipo" name="tipo">
        <option value="todos">Todos</option>
        @foreach (\App\Models\Menu::TIPOS as $clave => $nombre)
          <option value="{{ $clave }}" @selected($filtros['tipo'] === $clave)>{{ $nombre }}</option>
        @endforeach
      </select>
      <label for="distancia">Distancia al campus</label>
      <select id="distancia" name="distancia">
        <option value="999999" @selected($filtros['distancia'] === 999999)>Cualquiera</option>
        <option value="200" @selected($filtros['distancia'] === 200)>Hasta 200 m</option>
        <option value="500" @selected($filtros['distancia'] === 500)>Hasta 500 m</option>
        <option value="1000" @selected($filtros['distancia'] === 1000)>Hasta 1 km</option>
      </select>
      <label for="q">Buscar por nombre</label>
      <input type="search" id="q" name="q" value="{{ $filtros['q'] }}" maxlength="60" placeholder="Ej: lomo saltado">
      <input type="hidden" name="disp" value="0">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" id="disp" name="disp" value="1" @checked($filtros['disp'])>
        <label class="form-check-label" for="disp">Solo menús disponibles</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" id="rem" name="rem" value="1" @checked($filtros['rem'])>
        <label class="form-check-label" for="rem">Solo con remate solidario</label>
      </div>
      <noscript><button type="submit" class="btn btn-principal">Buscar</button></noscript>
      <a class="btn btn-principal" href="{{ route('inicio') }}" id="btnLimpiar">Limpiar filtros</a>
    </form>
  </div>
</section>

<section class="seccion" aria-labelledby="tituloResultados">
  <div class="seccion-cabecera">
    <h2 id="tituloResultados">Menús disponibles</h2>
    <p id="resumen" aria-live="polite">{{ $menus->count() }} resultado(s) hasta S/. {{ number_format($filtros['precio'], 2) }}</p>
  </div>
  <div class="rejilla" id="resultados">
    @include('menus._resultados')
  </div>
</section>

<!-- Invitación para los dueños de locales -->
<section class="franja cta" aria-labelledby="tituloCta">
  <div class="seccion-cabecera">
    <h2 id="tituloCta">¿Tienes un local cerca del campus?</h2>
    <p>Publica tu menú del día y los remates de la tarde para que los estudiantes te encuentren y reserven.</p>
    @if (auth()->user()?->esEstablecimiento())
      <a class="btn btn-principal" href="{{ route('panel.menus.index') }}">Administrar mi local</a>
    @else
      <a class="btn btn-principal" href="{{ route('registro', ['rol' => 'establecimiento']) }}">Registrar mi local</a>
    @endif
  </div>
</section>
@endsection
