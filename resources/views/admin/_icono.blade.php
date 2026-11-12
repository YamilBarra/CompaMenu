{{--
    Iconos SVG de las tarjetas del resumen (estilo línea, sin librerías). Recibe $nombre.
    El tamaño, el grosor y el color salen de .metrica-icono en estilos.css (usa currentColor).
--}}
@switch($nombre)
  @case('estudiantes')
    <svg class="metrica-icono" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M21.42 10.92a1 1 0 0 0-.02-1.84L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.83l8.57 3.91a2 2 0 0 0 1.66 0z"/><path d="M22 10v6"/><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/></svg>
    @break
  @case('establecimientos')
    <svg class="metrica-icono" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3 9l1.5-5h15L21 9"/><path d="M3 9a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/><path d="M5 12v8h14v-8"/><path d="M10 20v-5h4v5"/></svg>
    @break
  @case('menus')
    <svg class="metrica-icono" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3 2v7a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6a2 2 0 0 0 2 2h3z"/><path d="M21 15v7"/></svg>
    @break
  @case('remates')
    <svg class="metrica-icono" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12.59 2.59A2 2 0 0 0 11.17 2H4a2 2 0 0 0-2 2v7.17a2 2 0 0 0 .59 1.41l8.7 8.7a2.43 2.43 0 0 0 3.42 0l6.58-6.58a2.43 2.43 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r="1" fill="currentColor"/></svg>
    @break
  @case('valoraciones')
    <svg class="metrica-icono" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
    @break
  @case('promedio')
    <svg class="metrica-icono" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
    @break
  @case('reservas')
    <svg class="metrica-icono" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
    @break
  @case('pendientes')
    <svg class="metrica-icono" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
    @break
@endswitch