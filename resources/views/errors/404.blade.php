{{--
    Página de error 404: el recurso no existe (también se usa cuando un local intenta tocar datos de otro).
--}}
@extends('layouts.app')
@section('titulo', 'No encontramos esa página')

@section('contenido')
<section class="error-pagina">
  <h1>404</h1>
  <h2>No encontramos esa página</h2>
  <p>Puede que el enlace esté mal escrito o que el menú ya no exista.</p>
  <a class="btn btn-principal" href="{{ route('inicio') }}">Volver al inicio</a>
</section>
@endsection
