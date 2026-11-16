# Optimización de consultas y seguridad contra SQL Injection

Cambios aplicados en CompaMenu a partir de la **Semana 11 (Herramientas y técnicas de optimización de consultas en Laravel)** y de la charla **Seguridad en aplicaciones web (OWASP Top 10)**.

## 1. Optimización de consultas (Semana 11)

| Técnica (diapositiva) | Dónde se aplicó | Qué mejora |
|---|---|---|
| **Índices** (sección 3) | `database/migrations/2026_11_16_000001_add_indices_optimizacion.php` | Índices compuestos para el buscador (`locales: activo + distancia`), el puntaje (`valoraciones: local + visible + puntaje`, índice cubierto), remates, platos, reservas y usuarios |
| **Agregaciones en la base** (Query Rewriting, ejemplo 4) | `Admin/ResumenController` | Los gráficos usaban `pluck()->countBy()`, que traía **todas** las filas a PHP. Ahora usan `COUNT(*) ... GROUP BY` |
| **Caching** (sección 6) | `app/Support/CacheConsulta.php` + `Admin/ResumenController` | Las métricas del admin se guardan 2 min y se invalidan solas al guardar o borrar datos |
| **Invalidación automática** (6.2) | `AppServiceProvider::invalidarCacheAlCambiarDatos()` | Eventos `saved`/`deleted` de los modelos cambian la versión del caché |
| **Select específico / eager loading con columnas** (técnica 4) | Controladores de `Admin/*` y `ResenaController` | `with('user:id,name')` en vez de traer todas las columnas |
| **Slow query log** (5.3 y 5.4) | `AppServiceProvider::monitorearConsultas()` | Las consultas de más de 100 ms (la meta de «Métricas clave») se guardan en `storage/logs/laravel.log` |
| **Detección de N+1** (sección 2) | `AppServiceProvider::monitorearConsultas()` | `Model::preventLazyLoading()` avisa en el log si una vista carga relaciones dentro de un bucle |
| **Exists vs Count** (técnica 6) | `MenuController::show` (ya existía) | `exists()` en lugar de `count() > 0` |
| **withCount / withAvg** (ejemplo 4) | `Local::scopeConPuntaje` (ya existía) | Puntaje y reseñas en la misma consulta del listado |

### Cómo comprobarlo

```bash
php artisan migrate                                   # crea los índices nuevos
php artisan test --filter=SeguridadOptimizacionTest   # pruebas automáticas
```

En MySQL, para ver que el buscador usa un índice (columna `key` distinta de `NULL`):

```sql
EXPLAIN SELECT * FROM locales WHERE activo = 1 AND distancia_campus <= 500;
```

## 2. SQL Injection

CompaMenu **no tenía** la vulnerabilidad de la diapositiva (texto del usuario pegado dentro del SQL), porque todas las consultas usan Eloquent o `whereRaw(..., [bindings])`. Lo que se agregó es una defensa en capas y la forma de comprobarla:

1. **Sentencias preparadas**: Eloquent y los `?` de `whereRaw` envían los datos por separado del comando, así que la base nunca confunde datos con SQL.
2. **Validación de la entrada**: `Texto::limpio()` (quita HTML), `Texto::paraLike()` (escapa `%` y `_`), `validate()` con `email`, `Rule::in`, `integer`, etc.
3. **Parámetros de URL solo numéricos** (nuevo): `Route::pattern(...)` en `AppServiceProvider`. `/menus/1 OR 1=1` responde 404 y ni llega a la consulta. En MySQL, comparar un `id` con el texto `'1 OR 1=1'` lo convertiría a `1` en silencio; el patrón lo evita.
4. **Pruebas con payloads reales** (nuevo): `tests/Feature/SeguridadOptimizacionTest.php` envía `' OR 1=1 --`, `'; DROP TABLE users; --` y un `UNION SELECT` al buscador, a locales, a la búsqueda de usuarios del admin, al login y a las URLs.
5. **Revisión estática (SAST sencillo)** (nuevo): un test recorre `app/` y falla si alguien construye SQL concatenando o interpolando variables dentro de `whereRaw`, `selectRaw`, `orderByRaw`, `DB::select`, etc.

### Regla para el equipo

```php
// MAL: la entrada del usuario forma parte del SQL
User::whereRaw("name = '$nombre'")->get();

// BIEN: bindings (el valor viaja aparte)
User::whereRaw('LOWER(name) = ?', [$nombre])->get();
User::where('name', $nombre)->get();
```
