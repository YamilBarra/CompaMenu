{{--
    Resumen del administrador. Ruta: GET /admin.
    Recibe $metricas, $ultimasReservas, $ultimasResenas y $graficos (datos ya calculados en el controlador).
    Los gráficos son SVG y CSS puro (sin librerías): se ven bien en PC y celular.
--}}
@extends('layouts.app')
@section('titulo', 'Administración')

@section('contenido')
@include('admin._nav')
@php
  $totalReservas = collect($graficos['estados'])->sum('total');
  $maxDia = max(1, collect($graficos['dias'])->max('total'));
  $promedio = (float) $metricas['promedio'];
@endphp
<section class="seccion panel-cuerpo">
  <div class="seccion-cabecera"><h2>Resumen de CompaMenu</h2></div>

  {{-- Tarjetas de métricas --}}
  <div class="metricas">
    <div class="metrica">@include('admin._icono', ['nombre' => 'estudiantes'])<strong>{{ $metricas['estudiantes'] }}</strong><span>Estudiantes registrados</span></div>
    <div class="metrica">@include('admin._icono', ['nombre' => 'establecimientos'])<strong>{{ $metricas['establecimientos'] }}</strong><span>Establecimientos</span></div>
    <div class="metrica">@include('admin._icono', ['nombre' => 'menus'])<strong>{{ $metricas['menus'] }}</strong><span>Menús publicados</span></div>
    <div class="metrica">@include('admin._icono', ['nombre' => 'remates'])<strong>{{ $metricas['remates'] }}</strong><span>Remates publicados</span></div>
    <div class="metrica">@include('admin._icono', ['nombre' => 'valoraciones'])<strong>{{ $metricas['valoraciones'] }}</strong><span>Valoraciones</span></div>
    <div class="metrica">
      @include('admin._icono', ['nombre' => 'promedio'])<strong>{{ $metricas['promedio'] ?: '—' }}</strong><span>Promedio de estrellas</span>
      <span class="metrica-estrellas" role="img" aria-label="{{ $promedio }} de 5 estrellas"><span style="width: {{ round($promedio / 5 * 100) }}%">★★★★★</span>★★★★★</span>
    </div>
    <div class="metrica">@include('admin._icono', ['nombre' => 'reservas'])<strong>{{ $metricas['reservas'] }}</strong><span>Reservas</span></div>
    <div class="metrica @if ($metricas['pendientes'] > 0) metrica-alerta @endif">@include('admin._icono', ['nombre' => 'pendientes'])<strong>{{ $metricas['pendientes'] }}</strong><span>Reservas pendientes</span></div>
  </div>

  {{-- Gráficos de reservas --}}
  <div class="dos-columnas">
    <div class="caja">
      <h3>Reservas por estado</h3>
      @if ($totalReservas === 0)
        <p class="sin-resenas">Aún no hay reservas.</p>
      @else
        <div class="dona-contenedor">
          @php($acumulado = 0)
          <svg class="dona" viewBox="0 0 42 42" role="img" aria-label="Gráfico circular de reservas por estado">
            <circle cx="21" cy="21" r="15.9155" fill="none" stroke="#f1e4dc" stroke-width="7"></circle>
            @foreach ($graficos['estados'] as $e)
              @if ($e['total'] > 0)
                @php($parte = $e['total'] / $totalReservas * 100)
                <circle cx="21" cy="21" r="15.9155" fill="none" stroke="{{ $e['color'] }}" stroke-width="7"
                        stroke-dasharray="{{ round($parte, 3) }} {{ round(100 - $parte, 3) }}"
                        stroke-dashoffset="{{ round(25 - $acumulado, 3) }}"><title>{{ $e['nombre'] }}: {{ $e['total'] }}</title></circle>
                @php($acumulado += $parte)
              @endif
            @endforeach
            <text x="21" y="20.5" text-anchor="middle" class="dona-total">{{ $totalReservas }}</text>
            <text x="21" y="26" text-anchor="middle" class="dona-etiqueta">reservas</text>
          </svg>
          <ul class="leyenda">
            @foreach ($graficos['estados'] as $e)
              <li><i style="background: {{ $e['color'] }}"></i>{{ $e['nombre'] }} <strong>{{ $e['total'] }}</strong>
                <span class="ayuda">({{ round($e['total'] / $totalReservas * 100) }}%)</span></li>
            @endforeach
          </ul>
        </div>
      @endif
    </div>

    <div class="caja">
      <h3>Reservas de los últimos 7 días</h3>
      <div class="columnas" role="img" aria-label="Gráfico de barras de reservas por día">
        @foreach ($graficos['dias'] as $d)
          <div class="columna @if ($d['hoy']) hoy @endif" title="{{ $d['fecha'] }}: {{ $d['total'] }} reserva(s)">
            <span class="columna-valor">{{ $d['total'] }}</span>
            <span class="columna-barra" style="height: {{ $d['total'] > 0 ? max(6, round($d['total'] / $maxDia * 100)) : 2 }}%"></span>
            <span class="columna-dia">{{ $d['etiqueta'] }}</span>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- Gráficos de menús, valoraciones y locales --}}
  <div class="tres-columnas">
    <div class="caja">
      <h3>Menús por tipo</h3>
      @include('admin._barras', ['items' => $graficos['tipos'], 'vacio' => 'Aún no hay menús.'])
    </div>
    <div class="caja">
      <h3>Valoraciones por puntaje</h3>
      @include('admin._barras', ['items' => $graficos['puntajes'], 'vacio' => 'Aún no hay valoraciones.'])
    </div>
    <div class="caja">
      <h3>Locales con más reservas</h3>
      @include('admin._barras', ['items' => $graficos['locales'], 'vacio' => 'Aún no hay reservas.'])
    </div>
  </div>

  <div class="dos-columnas">
    <div class="caja">
      <h3>Últimas reservas</h3>
      <ul class="mini-lista">
        @forelse ($ultimasReservas as $r)
          <li><span class="codigo">{{ $r->codigo }}</span> · {{ $r->menu->nombre }} <span class="estado estado-{{ $r->estado }}">{{ $r->estado_nombre }}</span>
            <span class="ayuda">{{ $r->user->name }} → {{ $r->local->nombre }}</span></li>
        @empty
          <li class="sin-resenas">Sin reservas todavía.</li>
        @endforelse
      </ul>
    </div>
    <div class="caja">
      <h3>Últimas valoraciones</h3>
      <ul class="mini-lista">
        @forelse ($ultimasResenas as $v)
          <li><span class="estrellas">{{ str_repeat('★', $v->puntaje) }}{{ str_repeat('☆', 5 - $v->puntaje) }}</span> {{ $v->local->nombre }}
            <span class="ayuda">{{ $v->user->name }}: {{ $v->comentario }}</span></li>
        @empty
          <li class="sin-resenas">Sin valoraciones todavía.</li>
        @endforelse
      </ul>
    </div>
  </div>
</section>
@endsection