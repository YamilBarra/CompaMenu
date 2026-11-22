<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Agrega cabeceras de seguridad a todas las respuestas web (OWASP A05: configuración de seguridad incorrecta).
 *
 * - X-Content-Type-Options: el navegador no "adivina" el tipo de archivo (evita ejecutar un archivo subido como script).
 * - X-Frame-Options: nadie puede mostrar CompaMenu dentro de un iframe (evita clickjacking).
 * - Referrer-Policy: no se envía la URL completa a otros sitios.
 * - Permissions-Policy: la página no pide cámara, micrófono ni geolocalización.
 * - Strict-Transport-Security: solo cuando la petición ya llegó por HTTPS (en XAMPP local con http no se envía).
 *
 * No se activa Content-Security-Policy porque las vistas cargan Bootstrap y Chart.js desde CDN y usan scripts propios;
 * se deja como mejora futura (ver docs/OPTIMIZACION-Y-SEGURIDAD.md).
 */
class CabecerasSeguridad
{
    public function handle(Request $request, Closure $next): Response
    {
        $respuesta = $next($request);

        $respuesta->headers->set('X-Content-Type-Options', 'nosniff');
        $respuesta->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $respuesta->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $respuesta->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if ($request->isSecure()) {
            $respuesta->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $respuesta;
    }
}
