<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
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
     * Se ejecuta al iniciar la aplicación: fija el idioma español, la paginación con Bootstrap 5
     * y comparte con la barra de navegación cuántas reservas pendientes tiene el local.
     */
    public function boot(): void
    {
        // Todo el sitio está en español (mensajes de validación, fechas)
        app()->setLocale('es');
        Carbon::setLocale('es');

        // La paginación usa las clases de Bootstrap 5
        Paginator::useBootstrapFive();

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
}
