{{--
    Mensajes flash: 'exito' (verde) y 'error' (rojo) que los controladores envían con ->with(...).
--}}
@if (session('exito') || session('error'))
  <div class="avisos" role="status">
    @if (session('exito'))
      <div class="alert alert-success">{{ session('exito') }}</div>
    @endif
    @if (session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
  </div>
@endif
