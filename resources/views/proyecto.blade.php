{{--
    Página 'Proyecto': problema, idea, equipo, cómo está hecho y créditos/licencias. Ruta: GET /proyecto.
--}}
@extends('layouts.app')
@section('titulo', 'Proyecto')
@section('descripcion', 'Sobre el proyecto CompaMenu')

@section('contenido')
<section class="encabezado-pagina">
  <div class="encabezado-interior">
    <h1>Por qué existe CompaMenu</h1>
    <p>Proyecto del curso Programación Web, Facultad de Ingeniería, Universidad Continental, Cusco.</p>
  </div>
</section>

<section class="seccion">
  <div class="rejilla tres">
    <article class="bloque">
      <h3>El problema</h3>
      <p>Muchos estudiantes foráneos pierden cerca de 30 minutos al mediodía buscando un menú barato que aún tenga comida.</p>
    </article>
    <article class="bloque">
      <h3>La idea</h3>
      <p>Una web ligera que muestra los menús del día de los locales cercanos, pensada para celulares básicos y poco internet. Ahora también permite reservar tu porción.</p>
    </article>
    <article class="bloque">
      <h3>El equipo</h3>
      <p>Cuatro estudiantes de ingeniería que combinan diseño, base de datos, seguridad y pruebas en un solo proyecto.</p>
    </article>
  </div>
</section>

<section class="seccion franja">
  <div class="seccion-cabecera"><h2>Cómo está hecho</h2></div>
  <div class="rejilla tres">
    <article class="bloque">
      <h3>Arquitectura MVC</h3>
      <p>Laravel separa las rutas, los controladores (lógica), los modelos (MySQL) y las vistas Blade (HTML, CSS y JavaScript).</p>
    </article>
    <article class="bloque">
      <h3>Seguridad</h3>
      <p>Consultas preparadas (sin SQL Injection), texto escapado en pantalla (sin XSS), contraseñas con hash y control de acceso por rol.</p>
    </article>
    <article class="bloque">
      <h3>Diseño andino</h3>
      <p>Bootstrap 5 como base responsive, con colores y franjas de telar propios y la fuente Alfa Slab One (licencia OFL).</p>
    </article>
  </div>
</section>

<!-- Créditos y licencias de los recursos externos (citación de librerías, fase 2 de la consigna) -->
<section class="seccion" aria-labelledby="tituloCreditos">
  <div class="seccion-cabecera"><h2 id="tituloCreditos">Créditos y licencias</h2></div>
  <div class="caja">
    <p>CompaMenu usa software libre. Cada recurso se emplea respetando su licencia:</p>
    <ul class="mini-lista">
      <li><strong>Laravel</strong> (framework, licencia MIT) · laravel.com</li>
      <li><strong>Bootstrap 5.3</strong> (maquetación responsive, licencia MIT) · getbootstrap.com</li>
      <li><strong>Alfa Slab One</strong> (tipografía de los títulos, licencia SIL Open Font License 1.1) · Google Fonts</li>
      <li><strong>MySQL / MariaDB</strong> (base de datos, GPL v2) · <strong>PHP</strong> (licencia PHP 3.01)</li>
    </ul>
    <p class="ayuda mt-2">Detalle completo y referencias en el archivo docs/REFERENCIAS-Y-LICENCIAS.md del repositorio.</p>
  </div>
</section>
@endsection
