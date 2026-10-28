{{--
    Lista de tarjetas del buscador. Recibe $menus.
    Es lo que HomeController devuelve dentro del JSON cuando el buscador consulta con fetch().
--}}
@forelse ($menus as $menu)
  @include('menus._tarjeta', ['menu' => $menu])
@empty
  <p class="vacio">No hay menús con esos filtros. Prueba subiendo el precio o ampliando la distancia.</p>
@endforelse
