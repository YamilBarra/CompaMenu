<div align="center">

# CompaMenu

### Menús económicos para estudiantes universitarios de Cusco

![Laravel](https://img.shields.io/badge/Laravel-11+-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-Fetch_API-F7DF1E?logo=javascript&logoColor=black)
![PHPUnit](https://img.shields.io/badge/Tests-PHPUnit-366488?logo=php&logoColor=white)

**Proyecto del curso Programación Web · Universidad Continental · NRC 39578 · Grupo 03**

<img src="docs/capturas/Figura-01-Inicio-con-buscador.png" alt="Página de inicio de CompaMenu" width="900">

</div>

---

## Contenido

- [Descripción](#descripción)
- [Tecnologías](#tecnologías)
- [Capturas de pantalla](#capturas-de-pantalla)
- [Instalación](#instalación)
- [Documentación técnica](#documentación-técnica)
- [Equipo](#equipo)

---

## Descripción

**CompaMenu** es una aplicación web con impacto social que conecta a los estudiantes con **pensiones, quioscos y comedores** cercanos al campus.

### Funcionalidades principales

- **Filtros de precio** para encontrar menús que se ajusten al bolsillo.
- **Remates de última hora** para aprovechar platos a precio reducido.
- **Reservas de platos** directamente desde la plataforma.
- **Reseñas de estudiantes** sobre porciones, higiene y atención.
- **Panel para dueños de local** con gestión de menús, remates y reservas.
- **Panel de administración** con resumen general, usuarios, locales y valoraciones.

### Roles del sistema

| Rol | Qué puede hacer |
|-----|-----------------|
| Estudiante | Buscar y filtrar menús, reservar platos, aprovechar remates y dejar reseñas |
| Dueño de local | Publicar menús y remates, atender reservas y editar los datos de su local |
| Administrador | Supervisar usuarios, locales, menús, valoraciones y reservas |

---

## Tecnologías

| Capa | Tecnología |
|------|------------|
| Backend | Laravel 11+ (PHP 8.2+) |
| Base de datos | MySQL |
| Vistas | Blade |
| Estilos | Bootstrap 5.3 |
| Interactividad | JavaScript (Fetch API) |
| Pruebas | PHPUnit |

---

## Capturas de pantalla

### Navegación pública

**Figura 01.** Inicio con buscador y filtros.

<img src="docs/capturas/Figura-01-Inicio-con-buscador.png" alt="Figura 01: Inicio con buscador" width="900">

**Figura 02 y 03.** Inicio en celular y menú desplegable.

<p>
  <img src="docs/capturas/Figura-02-Inicio-en-celular.png" alt="Figura 02: Inicio en celular" width="260">
  &nbsp;&nbsp;
  <img src="docs/capturas/Figura-03-Menu-desplegable-celular.png" alt="Figura 03: Menú desplegable en celular" width="300">
</p>

**Figura 04.** Detalle de un menú.

<img src="docs/capturas/Figura-04-Detalle-de-un-menu.png" alt="Figura 04: Detalle de un menú" width="900">

### Autenticación

**Figura 05 y 06.** Ingreso y registro.

<p>
  <img src="docs/capturas/Figura-05-Ingreso.png" alt="Figura 05: Ingreso" width="440">
  &nbsp;
  <img src="docs/capturas/Figura-06-Registro.png" alt="Figura 06: Registro" width="440">
</p>

### Experiencia del estudiante

**Figura 07.** Formulario de reserva.

<img src="docs/capturas/Figura-07-Formulario-de-reserva.png" alt="Figura 07: Formulario de reserva" width="900">

**Figura 08.** Mis reservas.

<img src="docs/capturas/Figura-08-Mis-reservas-estudiante.png" alt="Figura 08: Mis reservas del estudiante" width="900">

**Figura 09.** Reseñas de estudiantes.

<img src="docs/capturas/Figura-09-Resenas.png" alt="Figura 09: Reseñas" width="900">

**Figura 10.** Remate solidario.

<img src="docs/capturas/Figura-10-Remate-solidario.png" alt="Figura 10: Remate solidario" width="900">

### Panel del dueño de local

**Figura 11.** Gestión de menús.

<img src="docs/capturas/Figura-11-Panel-Mi-local-menus.png" alt="Figura 11: Panel Mi local, menús" width="900">

**Figura 12.** Edición de un menú.

<img src="docs/capturas/Figura-12-Panel-editar-menu.png" alt="Figura 12: Editar menú" width="900">

**Figura 13.** Remates del local.

<img src="docs/capturas/Figura-13-Panel-remates.png" alt="Figura 13: Remates" width="900">

**Figura 14.** Reservas recibidas.

<img src="docs/capturas/Figura-14-Panel-reservas-recibidas.png" alt="Figura 14: Reservas recibidas" width="900">

**Figura 15.** Confirmación antes de eliminar.

<img src="docs/capturas/Figura-15-Confirmacion-al-eliminar.png" alt="Figura 15: Confirmación al eliminar" width="900">

### Panel de administración

**Figura 16.** Resumen general.

<img src="docs/capturas/Figura-16-Administracion-resumen.png" alt="Figura 16: Administración, resumen" width="900">

**Figura 17.** Gestión de usuarios.

<img src="docs/capturas/Figura-17-Administracion-usuarios.png" alt="Figura 17: Administración, usuarios" width="900">

---

## Instalación

Los pasos para instalar y ejecutar el proyecto (XAMPP, terminal, base de datos, cuentas de prueba y pruebas automatizadas) están en la guía aparte:

**[Configuración rápida](CONFIGURACION-RAPIDA.md)**

---

## Documentación técnica

| Archivo | Contenido |
|---------|-----------|
| [`docs/ARQUITECTURA.md`](docs/ARQUITECTURA.md) | Arquitectura MVC, diagramas, modelo de datos, roles y decisiones de diseño |
| [`docs/REFERENCIAS-Y-LICENCIAS.md`](docs/REFERENCIAS-Y-LICENCIAS.md) | Citación de librerías y recursos externos, licencias y referencias |

> El código está comentado en español: cada método de controlador, modelo, middleware y request explica su propósito, su ruta y las reglas de seguridad que aplica; cada vista Blade indica qué muestra y qué variables recibe.

---

## Equipo

- Yamil Alejandro Barra Camacho
- José Daniel Araos Lima
- José Jair Curasco Aranzabal
- Edy Albert Lezama Quispe

---

<div align="center">

Universidad Continental · Cusco, Perú · 2026

</div>
