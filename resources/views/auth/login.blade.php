{{--
    Formulario de ingreso. Ruta: GET /ingresar (LoginController@show). Envía a POST /ingresar.
    Los errores de validación y de credenciales llegan en $errors (se muestran bajo el campo correo).
--}}
@extends('layouts.app')
@section('titulo', 'Ingresar')

@section('contenido')
<section class="auth">
  <div class="auth-caja">
    <form class="auth-tarjeta" method="POST" action="{{ route('login.enviar') }}" novalidate>
      @csrf
      <h1>Ingresar</h1>
      <p class="sub">Entra para reservar platos, dejar reseñas o administrar tu local.</p>

      <x-campo name="email" label="Correo" type="email" autocomplete="email" required autofocus />
      <x-campo name="password" label="Contraseña" type="password" autocomplete="current-password" required />

      <div class="form-check">
        <input class="form-check-input" type="checkbox" id="recordar" name="recordar" value="1" @checked(old('recordar'))>
        <label class="form-check-label" for="recordar">Mantener mi sesión iniciada</label>
      </div>

      <button type="submit" class="btn btn-principal btn-enviar">Ingresar</button>
      <p class="auth-pie">¿Aún no tienes cuenta? <a href="{{ route('registro') }}">Créala aquí</a></p>
    </form>

    @if (app()->environment('local'))
      <p class="cuentas-demo"><strong>Cuentas de prueba</strong> (contraseña <code>Compa2026!</code>):
        estudiante@compamenu.pe · inti@compamenu.pe (local) · admin@compamenu.pe (administrador)</p>
    @endif
  </div>
</section>
@endsection
