# CompaMenu: Configuración rápida

Guía para instalar y ejecutar CompaMenu en local con **Laravel 13 / PHP 8.4+**. Para la descripción del proyecto, capturas y documentación técnica, revisa el [README](README.md).

## Requisitos

- **Laravel 13 / PHP 8.4+** (con las extensiones `gd`, `fileinfo`, `openssl`, `pdo_mysql`, `mbstring`, `curl` y `zip` habilitadas)
- Composer
- Node.js y npm
- MySQL (XAMPP o servicio independiente)

> **Nota sobre la extensión `gd` (y `fileinfo`):** En instalaciones nuevas de PHP (especialmente en Windows), las extensiones `gd` y `fileinfo` suelen venir desactivadas por defecto y provocan errores al ejecutar `composer install`. Para habilitarlas:
> 1. Ejecuta `php --ini` en tu terminal para ubicar la ruta de tu archivo `php.ini` (*Loaded Configuration File*).
> 2. Abre ese archivo `php.ini`, busca las líneas `;extension=gd` y `;extension=fileinfo` y **elimina el punto y coma (`;`)** al inicio de cada una (`extension=gd` y `extension=fileinfo`).
> 3. Guarda los cambios y reinicia la terminal antes de instalar dependencias.

---

## 1. Configuración de la base de datos

Al copiar `.env.example` a `.env`, reemplaza el bloque de base de datos por los parámetros de MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=compaMenu
DB_USERNAME=root
DB_PASSWORD=
```

---

## 2. Cómo ejecutarlo

### Método 1: XAMPP y phpMyAdmin (visual)

**1. Iniciar servicios en XAMPP**

Abre el panel de control de XAMPP e inicia los módulos **Apache** y **MySQL**, o desde la terminal:

```bash
sudo /opt/lampp/lampp start
```

**2. Crear la base de datos en phpMyAdmin**

1. Entra a <http://localhost/phpmyadmin>.
2. Haz clic en **Nueva** (*New*) en la barra lateral izquierda.
3. En *Nombre de la base de datos* escribe exactamente: `compaMenu`.
4. En el cotejamiento selecciona `utf8mb4_unicode_ci` y presiona **Crear**.

**3. Instalar y preparar el proyecto**

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

**4. Poblar las tablas y compilar assets**

```bash
php artisan migrate:fresh --seed
npm run build
```

**5. Iniciar la aplicación**

```bash
php artisan serve
```

Abre la plataforma en <http://localhost:8000>.

---

### Método 2: Ejecución rápida por terminal (CLI)

```bash
# 1. Asegurar el servicio de MySQL activo y crear la base de datos
mysql -u root -e "CREATE DATABASE IF NOT EXISTS compaMenu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Dependencias y configuración
composer install
npm install
cp .env.example .env
php artisan key:generate

# 3. Migraciones, datos de prueba y arranque
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

---

## 3. Cuentas de prueba

> **Contraseña unificada para todas las cuentas:** `Compa2026!`

| Rol | Correo |
|-----|--------|
| Administrador general | `admin@compamenu.pe` |
| Estudiante universitario | `estudiante@compamenu.pe` |
| Dueño de local gastronómico | `inti@compamenu.pe` |

---

## 4. Pruebas automatizadas

```bash
php artisan test
```
