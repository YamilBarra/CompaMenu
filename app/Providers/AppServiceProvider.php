<?php

namespace App\Providers;

use App\Models\Local;
use App\Models\Menu;
use App\Models\Remate;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Valoracion;
use App\Support\CacheConsulta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * No se registran servicios propios por ahora.
     */
    public function register(): void
    {
        //
    }

    /**
     * Se ejecuta al iniciar la aplicación: fija el idioma español, la paginación con Bootstrap 5,
     * comparte con la barra de navegación cuántas reservas pendientes tiene el local y activa las
     * herramientas de seguridad y de monitoreo de consultas (Semana 11).
     */
    public function boot(): void
    {
        // Todo el sitio está en español (mensajes de validación, fechas)
        app()->setLocale('es');
        Carbon::setLocale('es');

        // La paginación usa las clases de Bootstrap 5
        Paginator::useBootstrapFive();

        $this->protegerParametrosDeRuta();
        $this->monitorearConsultas();
        $this->invalidarCacheAlCambiarDatos();

        // La barra de navegación muestra cuántas reservas pendientes tiene el local
        View::composer('layouts.app', function ($vista) {
            $usuario = auth()->user();
            $pendientes = 0;
            if ($usuario && $usuario->esEstablecimiento() && $usuario->local) {
                $pendientes = $usuario->local->reservas()->where('estado', 'pendiente')->count();
            }
            $vista->with('pendientesLocal', $pendientes);
        });
    }

    /**
     * Seguridad (OWASP A05 Inyección): los identificadores de la URL deben ser números.
     * Una URL como /menus/1%20OR%201=1 ni siquiera llega a la consulta: responde 404.
     * Se aplica a todas las rutas que usan estos parámetros (públicas, panel y admin).
     */
    private function protegerParametrosDeRuta(): void
    {
        foreach (['menu', 'local', 'remate', 'reserva', 'valoracion', 'usuario'] as $parametro) {
            Route::pattern($parametro, '[0-9]+');
        }
    }

    /**
     * Semana 11 · Herramientas de análisis (sección 5): detecta consultas lentas y problemas N+1.
     *
     * - Consultas que tardan más de 100 ms (el objetivo de la tabla "Métricas clave") se guardan en
     *   storage/logs/laravel.log con el SQL y sin los valores (los bindings pueden traer datos personales).
     * - Si una vista carga una relación dentro de un bucle (problema N+1) se registra una advertencia
     *   con el modelo y la relación. No se lanza excepción: solo se avisa en el log, también en producción.
     */
    private function monitorearConsultas(): void
    {
        DB::listen(function ($consulta) {
            if ($consulta->time > 100) {
                Log::warning('Consulta lenta ('.round($consulta->time, 1).' ms): '.$consulta->sql);
            }
        });

        Model::preventLazyLoading();
        Model::handleLazyLoadingViolationUsing(function (Model $modelo, string $relacion) {
            Log::warning('Posible N+1: se cargó la relación "'.$relacion.'" de '.get_class($modelo).' dentro de un bucle. Usa with().');
        });
    }

    /**
     * Caché (sección 6.2): cuando se guarda o elimina un dato que alimenta las métricas del administrador
     * se cambia la versión del caché y el resumen se recalcula en la siguiente visita.
     */
    private function invalidarCacheAlCambiarDatos(): void
    {
        foreach ([User::class, Local::class, Menu::class, Remate::class, Reserva::class, Valoracion::class] as $modelo) {
            $modelo::saved(fn () => CacheConsulta::invalidar());
            $modelo::deleted(fn () => CacheConsulta::invalidar());
        }
    }
}
