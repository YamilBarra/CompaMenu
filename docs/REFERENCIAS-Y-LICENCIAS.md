# Referencias, licencias y citación de recursos externos

Este archivo cumple con la consigna de la Fase 2: *citación apropiada de librerías y recursos externos*.

## 1. Recursos externos usados

| Recurso | Versión | Uso | Licencia | Dónde está |
|---|---|---|---|---|
| Laravel | 13.x | Framework MVC del backend | MIT | Dependencia de Composer (`vendor/`, no se sube a Git) |
| Bootstrap | 5.3.8 | Rejilla, formularios, botones, alertas y paginación | MIT | `public/vendor/bootstrap/` (incluye `LICENSE`) |
| Alfa Slab One | — | Tipografía de los títulos | SIL Open Font License 1.1 | `public/fonts/alfa-slab-one.woff2` y `public/fonts/LICENSE-Alfa-Slab-One-OFL.txt` |
| PHP | 8.4+ | Lenguaje del servidor | PHP License 3.01 | Instalación del sistema |
| MySQL / MariaDB | — | Base de datos | GPL v2 | XAMPP |
| XAMPP y phpMyAdmin | — | Entorno local y administración de la base | GPL | Instalación local |
| PHPUnit | 12.x | Pruebas automáticas | BSD de 3 cláusulas | Dependencia de Composer |
| Git y GitHub | — | Control de versiones | GPL v2 (Git) | — |
| Fotografías de los platos (`public/img/*.jpg`) | — | Menús de ejemplo | **[COMPLETAR: autor o fuente y permiso]** | `public/img/` |
| Patrón de telar (`public/img/telar.svg`) y CSS propio | — | Identidad visual andina | Elaboración del equipo | `public/img/`, `public/css/` |

Las demás dependencias que instala Composer junto con Laravel usan licencias permisivas (MIT, BSD). Se pueden listar con `composer licenses`.

## 2. Referencias bibliográficas (APA 7)

Apache Friends. (s. f.). *XAMPP*. https://www.apachefriends.org/es/index.html

Bootstrap. (s. f.). *Bootstrap 5.3 documentation*. https://getbootstrap.com/docs/5.3/

Congreso de la República del Perú. (2011). *Ley N.° 29733, Ley de Protección de Datos Personales*. Diario Oficial El Peruano.

Google. (s. f.). *Alfa Slab One* [Fuente tipográfica]. Google Fonts. https://fonts.google.com/specimen/Alfa+Slab+One

Laravel. (s. f.). *Laravel documentation*. https://laravel.com/docs

MDN Web Docs. (s. f.). *Fetch API*. Mozilla. https://developer.mozilla.org/es/docs/Web/API/Fetch_API

Naciones Unidas. (s. f.). *Objetivos de Desarrollo Sostenible*. https://www.un.org/sustainabledevelopment/es/

Oracle. (s. f.). *MySQL reference manual*. https://dev.mysql.com/doc/

OWASP Foundation. (2021). *OWASP Top 10:2021*. https://owasp.org/Top10/

PHP Documentation Group. (s. f.). *PHP manual*. https://www.php.net/manual/es/

## 3. Cómo se cita dentro del código

- **Comentarios `@see`**: cuando una técnica sigue la documentación de un tercero, el comentario del método incluye el enlace
  (por ejemplo, el límite de intentos de ingreso en `LoginController@login` o el bloqueo de filas en `ReservaController@store`).
- **Encabezados de archivo**: `public/js/app.js` y `public/css/estilos.css` listan sus referencias y créditos al inicio.
- **Licencias distribuidas con el código**: Bootstrap y la fuente se entregan con su archivo de licencia y su aviso de copyright.
- **Código de terceros**: si algún fragmento se adapta de otra fuente, el comentario debe decir `Adaptado de <autor, año, URL>`.
  Si no hay una cita así, el código es del equipo.
- **Página pública**: la sección «Créditos y licencias» de la página *Proyecto* muestra los recursos principales a los usuarios.

## 4. Antes de agregar una librería o recurso nuevo

1. Confirmar que su licencia permite el uso académico y la redistribución.
2. Fijar la versión (no usar «latest») y guardar el archivo `LICENSE` junto al recurso.
3. Registrarlo en la tabla de la sección 1 y, si corresponde, en la sección 2.
4. Si es una imagen, fuente o sonido, anotar el autor y la fuente.

## 5. Declaración sobre herramientas de apoyo

**[COMPLETAR por el equipo, con honestidad]**: indicar si se usaron asistentes de inteligencia artificial u otras herramientas de apoyo
(qué herramienta y para qué partes: código, diseño, documentación) y confirmar que el equipo revisó, probó y comprende el resultado.
Consultar con el docente si el curso exige declararlo.
