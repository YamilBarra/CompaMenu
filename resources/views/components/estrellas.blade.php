{{--
    Componente <x-estrellas>: muestra el puntaje como estrellas (★★★★☆) y el número.
    Props: valor (promedio, puede ser null) y cantidad (total de reseñas, opcional).
    Si no hay valor muestra 'Aún sin reseñas'. Incluye aria-label para lectores de pantalla.
--}}
@props(['valor' => 0, 'cantidad' => null])
@if ($valor > 0)
  <span class="estrellas" role="img" aria-label="{{ number_format($valor, 1) }} de 5 estrellas">{{ str_repeat('★', (int) round($valor)) }}{{ str_repeat('☆', 5 - (int) round($valor)) }}</span>
  <span>{{ number_format($valor, 1) }}</span>@if ($cantidad)<span class="sin-resenas"> ({{ $cantidad }})</span>@endif
@else
  <span class="sin-resenas">Aún sin reseñas</span>
@endif
