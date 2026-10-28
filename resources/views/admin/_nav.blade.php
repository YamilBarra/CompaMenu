{{--
    Pestañas del panel de administración.
--}}
<nav class="subnav" aria-label="Administración">
  <a href="{{ route('admin.resumen') }}" @if (request()->routeIs('admin.resumen')) aria-current="page" @endif>Resumen</a>
  <a href="{{ route('admin.usuarios.index') }}" @if (request()->routeIs('admin.usuarios.*')) aria-current="page" @endif>Usuarios</a>
  <a href="{{ route('admin.locales.index') }}" @if (request()->routeIs('admin.locales.*')) aria-current="page" @endif>Locales</a>
  <a href="{{ route('admin.menus.index') }}" @if (request()->routeIs('admin.menus.*')) aria-current="page" @endif>Menús</a>
  <a href="{{ route('admin.valoraciones.index') }}" @if (request()->routeIs('admin.valoraciones.*')) aria-current="page" @endif>Valoraciones</a>
  <a href="{{ route('admin.reservas.index') }}" @if (request()->routeIs('admin.reservas.*')) aria-current="page" @endif>Reservas</a>
</nav>
<div class="subnav-linea"></div>
