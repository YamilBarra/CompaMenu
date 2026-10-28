{{--
    Pestañas del panel 'Mi local' (Menús, Remates, Reservas, Datos del local).
    La insignia de Reservas usa $pendientesLocal.
--}}
<nav class="subnav" aria-label="Mi local">
  <a href="{{ route('panel.menus.index') }}" @if (request()->routeIs('panel.menus.*')) aria-current="page" @endif>Menús</a>
  <a href="{{ route('panel.remates.index') }}" @if (request()->routeIs('panel.remates.*')) aria-current="page" @endif>Remates</a>
  <a href="{{ route('panel.reservas.index') }}" @if (request()->routeIs('panel.reservas.*')) aria-current="page" @endif>Reservas @if (($pendientesLocal ?? 0) > 0)<span class="insignia">{{ $pendientesLocal }}</span>@endif</a>
  <a href="{{ route('panel.local.edit') }}" @if (request()->routeIs('panel.local.*')) aria-current="page" @endif>Datos del local</a>
</nav>
<div class="subnav-linea"></div>
