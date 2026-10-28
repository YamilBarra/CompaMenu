{{--
    Página de error 403: el rol del usuario no tiene permiso (lo lanza VerificarRol).
--}}
@extends('layouts.app')
@section('titulo', 'Sin permiso')

@section('contenido')
<section class="error-pagina">
  <h1>403</h1>
  <h2>Sin permiso</h2>
  <p>Tu cuenta no tiene permiso para entrar a esta sección.</p>
  <a class="btn btn-principal" href="{{ route('inicio') }}">Volver al inicio</a>
</section>
@endsection
