{{--
    Barras horizontales reutilizables. Recibe $items: lista de ['nombre', 'total', 'color'] y, opcional, $vacio (texto).
    Son <div> con ancho en porcentaje: no usan librerías ni JavaScript.
--}}
@php($max = max(1, collect($items)->max('total')))
@if (collect($items)->sum('total') === 0)
  <p class="sin-resenas">{{ $vacio ?? 'Aún no hay datos.' }}</p>
@else
  <ul class="barras-h">
    @foreach ($items as $item)
      <li>
        <span class="barra-nombre">{{ $item['nombre'] }}</span>
        <span class="barra-pista"><span class="barra-relleno" style="width: {{ round($item['total'] / $max * 100) }}%; background: {{ $item['color'] }}"></span></span>
        <span class="barra-valor">{{ $item['total'] }}</span>
      </li>
    @endforeach
  </ul>
@endif
