<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Semana 11 · Optimización de consultas: índices para las consultas más frecuentes.
 *
 * Regla aplicada: indexar las columnas que aparecen en WHERE, JOIN y ORDER BY, con índices compuestos
 * cuando la consulta filtra por varias columnas a la vez. Se hace en una migración nueva (y no editando
 * las anteriores) para que quien ya tiene la base creada solo ejecute: php artisan migrate
 *
 * Cómo comprobar que se usan (en MySQL): EXPLAIN SELECT ... y revisar que "key" no sea NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Buscador de Inicio y lista de locales: WHERE activo = 1 AND distancia_campus <= ? / AND tipo_local = ?
        Schema::table('locales', function (Blueprint $table) {
            $table->index(['activo', 'distancia_campus'], 'locales_activo_distancia_idx');
            $table->index(['activo', 'tipo_local'], 'locales_activo_tipo_idx');
        });

        // Puntaje promedio y conteo de reseñas por local (withAvg / withCount con visible = 1).
        // Incluye "puntaje" para que el promedio se calcule solo con el índice (índice cubierto).
        // El segundo índice acelera la lista pública y la de moderación (ORDER BY created_at DESC).
        Schema::table('valoraciones', function (Blueprint $table) {
            $table->index(['local_id', 'visible', 'puntaje'], 'valoraciones_local_visible_puntaje_idx');
            $table->index(['visible', 'created_at'], 'valoraciones_visible_fecha_idx');
        });

        // Remate activo de cada menú (remateActivo, Remate::vigentes, filtro "solo remates")
        Schema::table('remates', function (Blueprint $table) {
            $table->index(['menu_id', 'activo', 'porciones'], 'remates_menu_activo_porciones_idx');
        });

        // Platos de un menú ordenados (relación Menu::platos, ORDER BY orden)
        Schema::table('platos', function (Blueprint $table) {
            $table->index(['menu_id', 'orden'], 'platos_menu_orden_idx');
        });

        // "Últimas reservas" del admin y gráfico de los últimos 7 días (WHERE/ORDER BY created_at)
        Schema::table('reservas', function (Blueprint $table) {
            $table->index('created_at', 'reservas_fecha_idx');
        });

        // Filtro de usuarios por rol (panel admin) y conteo de estudiantes
        Schema::table('users', function (Blueprint $table) {
            $table->index('rol', 'users_rol_idx');
        });
    }

    public function down(): void
    {
        Schema::table('locales', function (Blueprint $table) {
            $table->dropIndex('locales_activo_distancia_idx');
            $table->dropIndex('locales_activo_tipo_idx');
        });
        Schema::table('valoraciones', function (Blueprint $table) {
            $table->dropIndex('valoraciones_local_visible_puntaje_idx');
            $table->dropIndex('valoraciones_visible_fecha_idx');
        });
        // La clave foránea de menu_id usa estos índices compuestos: primero se crea uno simple y luego se borra el compuesto
        // (sin esto MySQL/MariaDB responde "needed in a foreign key constraint" y el rollback queda a medias).
        Schema::table('remates', function (Blueprint $table) {
            $table->index('menu_id', 'remates_menu_id_fk_idx');
        });
        Schema::table('remates', function (Blueprint $table) {
            $table->dropIndex('remates_menu_activo_porciones_idx');
        });
        Schema::table('platos', function (Blueprint $table) {
            $table->index('menu_id', 'platos_menu_id_fk_idx');
        });
        Schema::table('platos', function (Blueprint $table) {
            $table->dropIndex('platos_menu_orden_idx');
        });
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropIndex('reservas_fecha_idx');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_rol_idx');
        });
    }
};
