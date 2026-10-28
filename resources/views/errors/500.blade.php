{{--
    Página de error 500. Es HTML independiente (no usa el layout) para que se vea aunque la causa
    del error sea la base de datos o la sesión.
--}}
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Algo salió mal | CompaMenu</title>
<link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
</head>
<body>
<section class="error-pagina">
  <h1>500</h1>
  <h2>Algo salió mal</h2>
  <p>Tuvimos un problema en el servidor. Inténtalo en unos minutos.</p>
  <a class="btn btn-principal" href="/">Volver al inicio</a>
</section>
</body>
</html>
