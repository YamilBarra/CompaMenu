{{--
    Plantilla base de todas las páginas.
    Incluye: <head> (Bootstrap + estilos andinos), barra de navegación según el rol del usuario, mensajes flash,
    pie de página y el diálogo de confirmación (<dialog>) que usan los botones con data-confirmar.
    Secciones que rellenan las demás vistas: titulo, descripcion y contenido.
    Variable compartida: $pendientesLocal (la calcula el View Composer de AppServiceProvider).
--}}
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="@yield('descripcion', 'CompaMenu: menús económicos cerca de tu universidad en Cusco')">
<title>@yield('titulo', 'CompaMenu') | CompaMenu</title>
<!-- Bootstrap 5 (licencia MIT) guardado en el proyecto; después van nuestros estilos andinos -->
<link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
</head>
<body>
@php($usuario = auth()->user())

<!-- Barra de navegación -->
<header class="cabecera">
  <a href="{{ route('inicio') }}" class="logo">Compa<span>Menu</span></a>
  <button class="btn-menu" id="btnMenu" aria-label="Abrir menú" aria-expanded="false" aria-controls="nav">☰</button>
  <nav class="nav" id="nav" aria-label="Principal">
    <x-nav-link ruta="inicio">Inicio</x-nav-link>
    <x-nav-link ruta="locales.index" patron="locales.*">Locales</x-nav-link>
    <x-nav-link ruta="remates.index">Remate solidario</x-nav-link>
    <x-nav-link ruta="resenas.index">Reseñas</x-nav-link>
    <x-nav-link ruta="proyecto">Proyecto</x-nav-link>

    @if ($usuario?->esEstudiante())
      <x-nav-link ruta="reservas.mis" patron="reservas.*">Mis reservas</x-nav-link>
    @elseif ($usuario?->esEstablecimiento())
      <x-nav-link ruta="panel.menus.index" patron="panel.*">Mi local @if (($pendientesLocal ?? 0) > 0)<span class="insignia" title="Reservas pendientes">{{ $pendientesLocal }}</span>@endif</x-nav-link>
    @elseif ($usuario?->esAdmin())
      <x-nav-link ruta="admin.resumen" patron="admin.*">Administración</x-nav-link>
    @endif

    @auth
      <span class="usuario">Hola, <b>{{ \Illuminate\Support\Str::of($usuario->name)->explode(' ')->first() }}</b></span>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-borde btn-chico">Salir</button>
      </form>
    @else
      <a class="btn btn-borde btn-chico" href="{{ route('login') }}">Ingresar</a>
      <a class="btn btn-principal btn-chico" href="{{ route('registro') }}">Crear cuenta</a>
    @endauth
  </nav>
</header>

@include('partials.avisos')

<!-- Contenido principal de la página -->
<main>
@yield('contenido')
</main>

<!-- Pie de página -->
<footer class="pie">
  <p class="pie-lema">Cultura peruana en cada plato</p>
  <p>CompaMenu · Programación Web · NRC 39578 · Cusco, Perú · 2026</p>
</footer>

<!-- Ventana para confirmar antes de eliminar (la usan todos los botones con data-confirmar) -->
<dialog id="dlgConfirmar" class="dialogo" aria-labelledby="dlgTitulo">
  <div class="dialogo-cuerpo">
    <h3 id="dlgTitulo">¿Estás seguro?</h3>
    <p id="dlgTexto"></p>
    <div class="dialogo-botones">
      <button type="button" class="btn btn-borde oscuro" id="dlgNo">Cancelar</button>
      <button type="button" class="btn btn-peligro" id="dlgSi">Confirmar</button>
    </div>
  </div>
</dialog>

<script src="{{ asset('js/app.js') }}" defer></script>
@stack('scripts')
</body>
</html>
