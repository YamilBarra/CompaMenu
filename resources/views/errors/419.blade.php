{{--
    Página de error 419: el token CSRF caducó (la sesión expiró antes de enviar el formulario).
--}}
@extends('layouts.app')
@section('titulo', 'La página expiró')

@section('contenido')
<section class="error-pagina">
  <h1>419</h1>
  <h2>La página expiró</h2>
  <p>Tu sesión caducó. Recarga la página e inténtalo de nuevo.</p>
  <a class="btn btn-principal" href="{{ route('inicio') }}">Volver al inicio</a>
</section>
@endsection
