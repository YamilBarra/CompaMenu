# CompaMenu: Configuración rápida

Guía para instalar y ejecutar CompaMenu en tu computadora. Para la descripción del proyecto, capturas y documentación técnica, revisa el [README](README.md).

## 1. Qué necesitas instalado

| Programa | Versión | Para qué sirve |
|----------|---------|----------------|
| **PHP** | **8.3 o superior** (8.3, 8.4 u 8.5) | Ejecuta el proyecto (Laravel 13) |
| **Composer** | 2.x | Descarga las librerías de PHP que usa el proyecto |
| **MySQL** (viene en XAMPP) | cualquiera reciente | Guarda los datos (usuarios, menús, reservas) |

**No necesitas Node.js ni npm.** El proyecto ya trae sus estilos y scripts listos en la carpeta `public/`.

> **Importante: la versión de PHP.** Con PHP 8.2 o anterior, `composer install` falla con un mensaje parecido a *"Your PHP version (8.2.x) does not satisfy that requirement"*. Comprueba tu versión con:
>
> ```bash
> php -v
> ```

### Si usas XAMPP

- El XAMPP con **PHP 8.2 no sirve** para este proyecto. Necesitas un XAMPP que incluya **PHP 8.3 o superior** (descárgalo de <https://www.apachefriends.org/>).
- Alternativa: instala PHP 8.3+ por separado y usa XAMPP solo para MySQL y phpMyAdmin.
- **Solo si `php -v` falla o muestra una versión distinta a la de XAMPP** (en Windows): agrega `C:\xampp\php` al PATH y abre la terminal de nuevo.

### Extensiones de PHP

Deben estar activas: `gd`, `fileinfo`, `openssl`, `pdo_mysql`, `mbstring`, `curl` y `zip`. **XAMPP ya las trae activadas, así que normalmente no tienes que hacer nada.** Este paso es solo si `composer install` te dice que falta una extensión (suele pasar con `gd` o `fileinfo` en instalaciones nuevas de PHP, sobre todo en Windows). Para activarlas:

1. Ejecuta `php --ini` y mira la línea *Loaded Configuration File* (la ruta de tu `php.ini`).
2. Abre ese `php.ini`, busca `;extension=gd` y `;extension=fileinfo` y **borra el `;`** del inicio de cada una.
3. Guarda y reinicia la terminal.

---

## 2. Pasos de instalación

Abre una terminal **dentro de la carpeta del proyecto** y sigue los pasos en orden.

### Paso 1: Iniciar MySQL

Abre XAMPP e inicia **MySQL** (y **Apache** si quieres usar phpMyAdmin).

### Paso 2: Crear la base de datos

1. Entra a <http://localhost/phpmyadmin>.
2. Clic en **Nueva**.
3. Nombre: `compaMenu` (exactamente así, con esa M mayúscula).
4. Cotejamiento: `utf8mb4_unicode_ci`. Clic en **Crear**.

### Paso 3: Instalar las librerías de PHP

```bash
composer install
```

*Descarga las librerías que necesita el proyecto y las guarda en la carpeta `vendor/`. Puede tardar unos minutos.*

### Paso 4: Crear el archivo de configuración `.env`

```bash
# Windows (CMD o PowerShell)
copy .env.example .env

# Linux / Mac
cp .env.example .env
```

*Crea tu archivo de configuración personal a partir de la plantilla.*

Abre el archivo `.env` y busca el bloque `DB_`. Déjalo así (reemplaza lo que haya):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=compaMenu
DB_USERNAME=root
DB_PASSWORD=
```

*Le dice al proyecto cómo conectarse a la base de datos que creaste. Si tu MySQL tiene contraseña, escríbela en `DB_PASSWORD`.*

### Paso 5: Generar la clave de la aplicación

```bash
php artisan key:generate
```

*Crea una clave secreta aleatoria y la escribe en tu `.env` (línea `APP_KEY=`). Laravel la usa para cifrar las sesiones y las cookies. Es obligatorio hacerlo una vez; sin esa clave la aplicación muestra un error. No tienes que guardarla ni copiarla en ningún lado.*

### Paso 6: Crear las tablas y cargar datos de prueba

```bash
php artisan migrate:fresh --seed
```

*Crea todas las tablas en la base `compaMenu` y carga datos de ejemplo (locales, menús, platos y las cuentas de prueba). Si lo vuelves a ejecutar, **borra todo y empieza de cero**.*

### Paso 7: Iniciar la aplicación

```bash
php artisan serve
```

*Levanta el servidor. Deja esta terminal abierta mientras uses el sitio.*

Abre <http://localhost:8000> en el navegador.

---

### Resumen (para copiar y pegar)

Con MySQL ya iniciado y la base `compaMenu` ya creada:

```bash
composer install
cp .env.example .env        # en Windows: copy .env.example .env
# edita .env con los datos DB_ de arriba
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

---

## 3. Cuentas de prueba

> **Contraseña de todas las cuentas:** `Compa2026!`

| Rol | Correo |
|-----|--------|
| Administrador general | `admin@compamenu.pe` |
| Estudiante universitario | `estudiante@compamenu.pe` |
| Dueño de local gastronómico | `inti@compamenu.pe` |

---

## 4. Problemas frecuentes

| Problema | Solución |
|----------|----------|
| `composer install` dice que tu versión de PHP no cumple | Necesitas PHP 8.3 o superior (ver sección 1) |
| Error por falta de la extensión `gd` o `fileinfo` | Actívalas en `php.ini` (ver sección 1) |
| `No application encryption key has been specified` | Falta el Paso 5: ejecuta `php artisan key:generate` |
| `SQLSTATE ... Unknown database 'compaMenu'` | Falta el Paso 2, o el nombre no es exactamente `compaMenu` |
| `Connection refused` al migrar | MySQL no está iniciado (Paso 1) o los datos `DB_` del `.env` están mal |

---

## 5. Pruebas automatizadas (opcional)

```bash
php artisan test
```

Si te sale un error sobre `/dev/tty` (puede pasar en algunas terminales de Linux o Mac), usa:

```bash
php artisan test --without-tty
```