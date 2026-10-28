# CompaMenu

Red web de menús económicos para estudiantes universitarios de Cusco. Proyecto del curso **Programación Web**
(Universidad Continental, NRC 39578, Grupo 03): una aplicación con impacto social que conecta a los estudiantes con
pensiones, quioscos y comedores cercanos al campus, con filtros de precio, remates de última hora y reservas de platos.

## Tecnologías

Laravel 13 (PHP 8.4+) · MySQL · Blade · Bootstrap 5.3 · JavaScript (Fetch API) · PHPUnit

## Cómo ejecutarlo

```bash
composer install
cp .env.example .env          # ya viene configurado para MySQL: base compaMenu, usuario root, sin contraseña
php artisan key:generate
php artisan migrate:fresh --seed   # crea las tablas y los datos de ejemplo (MySQL encendido)
php artisan serve                  # http://localhost:8000
```

Cuentas de prueba (contraseña `Compa2026!`): `admin@compamenu.pe`, `estudiante@compamenu.pe`, `inti@compamenu.pe` (local).
Pruebas automáticas: `php artisan test`.

## Documentación

| Archivo | Contenido |
|---|---|
| [`docs/ARQUITECTURA.md`](docs/ARQUITECTURA.md) | Arquitectura MVC, diagramas, modelo de datos, roles, decisiones de diseño |
| [`docs/REFERENCIAS-Y-LICENCIAS.md`](docs/REFERENCIAS-Y-LICENCIAS.md) | Citación de librerías y recursos externos, licencias y referencias |
| [`LEEME-COMPAMENU.md`](LEEME-COMPAMENU.md) | Guía de instalación y uso |

El código está comentado en español: cada método de controlador, modelo, middleware y request explica su propósito,
su ruta y las reglas de seguridad que aplica; cada vista Blade indica qué muestra y qué variables recibe.

## Equipo

Yamil Alejandro Barra Camacho · José Daniel Araos Lima · José Jair Curasco Aranzabal · Edy Albert Lezama Quispe
