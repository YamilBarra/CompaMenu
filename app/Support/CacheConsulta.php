<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Caché de consultas pesadas con invalidación automática (Semana 11, sección 6: Caching de consultas).
 *
 * Cada clave lleva una "versión". Cuando se guarda o elimina cualquier modelo importante
 * (ver AppServiceProvider), la versión cambia y las claves viejas dejan de leerse: así el panel
 * nunca muestra datos desactualizados y no hace falta llamar a Cache::forget() en cada controlador.
 * Funciona con cualquier driver (database, file, redis), incluso los que no soportan "tags".
 */
class CacheConsulta
{
    private const CLAVE_VERSION = 'compamenu.version';

    /** Versión actual del caché. */
    public static function version(): string
    {
        return (string) Cache::get(self::CLAVE_VERSION, '0');
    }

    /** Invalida todo lo guardado con recordar(): se llama cuando cambian los datos. */
    public static function invalidar(): void
    {
        Cache::forever(self::CLAVE_VERSION, uniqid('', true));
    }

    /**
     * Devuelve el valor guardado o ejecuta $consulta y lo guarda por $segundos (Cache::remember).
     * Solo se deben guardar datos simples (arrays y números), no modelos de Eloquent.
     */
    public static function recordar(string $clave, int $segundos, Closure $consulta): mixed
    {
        return Cache::remember('compamenu.'.$clave.'.v'.self::version(), $segundos, $consulta);
    }
}
