<?php

namespace App\Support;

/** Limpieza simple de textos que escribe el usuario (sanitización de entradas, sección 5.5). */
class Texto
{
    /** Quita etiquetas HTML y espacios sobrantes. Devuelve null si queda vacío. */
    public static function limpio(?string $texto): ?string
    {
        if ($texto === null) {
            return null;
        }
        $texto = trim(strip_tags($texto));

        return $texto === '' ? null : $texto;
    }

    /** Escapa los comodines de LIKE (% y _) para que se busquen como texto normal. */
    public static function paraLike(string $texto): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $texto);
    }
}
