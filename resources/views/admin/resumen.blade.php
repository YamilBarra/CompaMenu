{{--
    Resumen del administrador. Ruta: GET /admin. Recibe $metricas, $ultimasReservas y $ultimasResenas.
--}}
@extends('layouts.app')
@section('titulo', 'Administración')

@section('contenido')
@include('admin._nav')
<section class="seccion panel-cuerpo">
  <div class="seccion-cabecera"><h2>Resumen de CompaMenu</h2></div>
  <div class="metricas">
    <div class="metrica"><strong>{{ $metricas['estudiantes'] }}</strong><span>Estudiantes registrados</span></div>
    <div class="metrica"><strong>{{ $metricas['establecimientos'] }}</strong><span>Establecimientos</span></div>
    <div class="metrica"><strong>{{ $metricas['menus'] }}</strong><span>Menús publicados</span></div>
    <div class="metrica"><strong>{{ $metricas['remates'] }}</strong><span>Remates publicados</span></div>
    <div class="metrica"><strong>{{ $metricas['valoraciones'] }}</strong><span>Valoraciones</span></div>
    <div class="metrica"><strong>{{ $metricas['promedio'] ?: '—' }}</strong><span>Promedio de estrellas</span></div>
    <div class="metrica"><strong>{{ $metricas['reservas'] }}</strong><span>Reservas</span></div>
    <div class="metrica"><strong>{{ $metricas['pendientes'] }}</strong><span>Reservas pendientes</span></div>
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
