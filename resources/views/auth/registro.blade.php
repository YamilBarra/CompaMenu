{{--
    Formulario de registro. Ruta: GET /registro (RegistroController@show).
    Recibe $rol ('estudiante' o 'establecimiento'). Los datos del local (#bloqueLocal) solo se ven si se elige
    establecimiento; esa lógica está en public/js/app.js (sección Registro).
--}}
@extends('layouts.app')
@section('titulo', 'Crear cuenta')

@section('contenido')
@php($rolActual = old('rol', $rol))
<section class="auth">
  <div class="auth-caja ancha">
    <form class="auth-tarjeta" method="POST" action="{{ route('registro.enviar') }}" id="formRegistro" novalidate>
      @csrf
      <h1>Crear cuenta</h1>
      <p class="sub">Elige cómo vas a usar CompaMenu.</p>

      <div class="roles" role="radiogroup" aria-label="Tipo de cuenta">
        <label class="rol-opcion">
          <input type="radio" name="rol" value="estudiante" @checked($rolActual !== 'establecimiento')>
          <span>Soy estudiante<small>Busco menús, reservo y opino</small></span>
        </label>
        <label class="rol-opcion">
          <input type="radio" name="rol" value="establecimiento" @checked($rolActual === 'establecimiento')>
          <span>Tengo un local<small>Publico menús y remates</small></span>
        </label>
      </div>
      @error('rol')<p class="error-campo">{{ $message }}</p>@enderror

      <div class="row g-3">
        <div class="col-md-6"><x-campo name="name" label="Nombre completo" autocomplete="name" required /></div>
        <div class="col-md-6"><x-campo name="telefono" label="Teléfono (opcional)" type="tel" autocomplete="tel" /></div>
      </div>
      <x-campo name="email" label="Correo" type="email" autocomplete="email" required />
      <div class="row g-3">
        <div class="col-md-6"><x-campo name="password" label="Contraseña" type="password" autocomplete="new-password" ayuda="Mínimo 8 caracteres, con letras y números." required /></div>
        <div class="col-md-6"><x-campo name="password_confirmation" label="Repite la contraseña" type="password" autocomplete="new-password" required /></div>
      </div>

      <!-- Estos datos solo se piden si la cuenta es de un local -->
      <div class="bloque-local" id="bloqueLocal" @if ($rolActual !== 'establecimiento') hidden @endif>
        <h2>Datos de tu local</h2>
        <x-campo name="local_nombre" label="Nombre del local" placeholder="Ej: Comedor Inti" />
        <div class="row g-3">
          <div class="col-md-6"><x-selector name="tipo_local" label="Tipo de local" :opciones="\App\Models\Local::TIPOS" value="comedor" /></div>
          <div class="col-md-6"><x-campo name="distancia_campus" label="Distancia al campus (m)" type="number" min="0" max="5000" step="10" inputmode="numeric" /></div>
        </div>
        <x-campo name="direccion" label="Dirección (opcional)" placeholder="Ej: Av. de la Cultura 123" />
      </div>

      <button type="submit" class="btn btn-principal btn-enviar">Crear mi cuenta</button>
      <p class="auth-pie">¿Ya tienes cuenta? <a href="{{ route('login') }}">Ingresa</a></p>
    </form>
  </div>
</section>
@endsection
