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

<img src="docs/capturas/01-inicio-escritorio.png" alt="Página de inicio de CompaMenu" width="900">

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

### Vista pública

**Inicio con filtros y menús disponibles**

<img src="docs/capturas/01-inicio-escritorio.png" alt="Inicio en escritorio" width="900">

**Inicio en móvil y menú de navegación desplegado**

<p>
  <img src="docs/capturas/02-inicio-movil.png" alt="Inicio en móvil" width="260">
  &nbsp;&nbsp;
  <img src="docs/capturas/03-menu-movil.png" alt="Menú de navegación móvil" width="300">
</p>

**Detalle de un menú**

<img src="docs/capturas/04-detalle-menu.png" alt="Detalle de un menú" width="900">

**Remate solidario**

<img src="docs/capturas/05-remate-solidario.png" alt="Remate solidario" width="900">

**Reseñas de estudiantes**

<img src="docs/capturas/06-resenas.png" alt="Reseñas" width="900">

### Autenticación

<p>
  <img src="docs/capturas/07-ingresar.png" alt="Ingresar" width="440">
  &nbsp;
  <img src="docs/capturas/08-crear-cuenta.png" alt="Crear cuenta" width="440">
</p>

### Panel del dueño de local

**Gestión de menús (nuevo menú)**

<img src="docs/capturas/09-local-menus.png" alt="Panel del local: menús" width="900">

**Edición de un menú**

<img src="docs/capturas/10-local-editar-menu.png" alt="Panel del local: editar menú" width="900">

**Remates del local**

<img src="docs/capturas/11-local-remates.png" alt="Panel del local: remates" width="900">

**Reservas recibidas**

<img src="docs/capturas/12-local-reservas.png" alt="Panel del local: reservas" width="900">

**Confirmación antes de eliminar**

<img src="docs/capturas/13-local-confirmar-eliminar.png" alt="Confirmación de eliminación" width="900">

### Panel de administración

**Resumen general**

<img src="docs/capturas/14-admin-resumen.png" alt="Administración: resumen" width="900">

**Gestión de usuarios**

<img src="docs/capturas/15-admin-usuarios.png" alt="Administración: usuarios" width="900">

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