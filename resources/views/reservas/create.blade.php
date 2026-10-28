{{--
    Formulario para reservar. Rutas: GET /reservar/menu/{menu} y GET /reservar/remate/{remate}.
    Recibe: $menu, $remate (null si es un menú normal), $local, $precio (normal o de remate), $stock,
    $ini y $fin (horario de recojo permitido). El total se calcula en el navegador (app.js) solo como ayuda;
    el precio real lo vuelve a calcular el servidor en ReservaController@store.
--}}
@extends('layouts.app')
@section('titulo', 'Reservar')

@section('contenido')
<section class="encabezado-pagina">
  <div class="encabezado-interior">
    <h1>Reservar {{ $remate ? 'a precio de remate' : 'tu menú' }}</h1>
    <p>Aparta tus porciones y recógelas en el local a la hora que elijas.</p>
  </div>
</section>

<section class="seccion">
  <div class="detalle">
    <div class="caja">
      <h3>{{ $menu->nombre }}</h3>
      <p class="local">{{ $local->nombre }} · {{ $local->distancia_campus }} m del campus</p>
      @if ($local->direccion)<p class="ayuda">{{ $local->direccion }}</p>@endif
      <p class="detalle-precio">
        @if ($remate)<small>S/. {{ number_format((float) $menu->precio, 2) }}</small>@endif
        S/. {{ number_format((float) $precio, 2) }}
      </p>
      <div class="etiquetas">
        <span class="etiqueta">{{ $menu->tipo_nombre }}</span>
        <span class="etiqueta">Quedan {{ $stock }} porciones</span>
        @if ($ini)<span class="etiqueta">Recojo {{ $fin ? "de $ini a $fin" : "desde $ini" }}</span>@endif
      </div>
      <a class="btn btn-borde oscuro btn-chico mt-3" href="{{ route('menus.show', $menu) }}">Ver detalle del menú</a>
    </div>

    <form class="caja" method="POST" action="{{ route('reservas.store') }}" id="formReserva" data-precio="{{ $precio }}" novalidate>
      @csrf
      <input type="hidden" name="menu_id" value="{{ $menu->id }}">
      @if ($remate)<input type="hidden" name="remate_id" value="{{ $remate->id }}">@endif
      <h3>Tus datos de recojo</h3>

      <x-campo name="cantidad" label="¿Cuántas porciones?" type="number" min="1" :max="min(5, $stock)" value="1" required />
      <x-campo name="hora_recojo" label="Hora de recojo" type="time" :value="$ini" :ayuda="$ini ? 'Entre '.$ini.($fin ? ' y '.$fin : ' en adelante') : null" required />
      <x-campo name="nota" label="Nota para el local (opcional)" maxlength="120" placeholder="Ej: sin ají, por favor" />

      <div class="reserva-resumen">
        Total a pagar en el local: <span class="total" id="totalReserva">S/. {{ number_format((float) $precio, 2) }}</span>
        <p class="ayuda">El pago se hace al recoger. Recibirás un código para mostrar.</p>
      </div>

      <button type="submit" class="btn btn-principal mt-3">Confirmar reserva</button>
    </form>
  </div>
</section>
@endsection
