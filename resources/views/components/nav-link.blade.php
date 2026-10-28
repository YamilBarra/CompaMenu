{{--
    Componente <x-nav-link>: enlace de la barra de navegación.
    Marca la página actual con aria-current="page" (accesibilidad y estilo).
    Props: ruta (nombre de la ruta) y patron (opcional, para marcar varias rutas como activas, ej. 'panel.*').
--}}
@props(['ruta', 'patron' => null])
<a href="{{ route($ruta) }}" @if (request()->routeIs($patron ?? $ruta)) aria-current="page" @endif {{ $attributes }}>{{ $slot }}</a>
