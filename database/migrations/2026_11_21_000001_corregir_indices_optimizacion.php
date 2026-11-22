<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Corrección de la migración 2026_11_16_000001_add_indices_optimizacion (medida con EXPLAIN y tiempos reales).
 *
 * 1) remates: el índice (menu_id, activo, porciones) hacía que MySQL/MariaDB eligiera un mal plan en el filtro
 *    "solo remates" del Inicio (≈1360 ms contra ≈20 ms sin ese índice). Se reemplaza por (activo, porciones, menu_id).
 *    Se deja además un índice simple en menu_id, que necesita la clave foránea.
 * 3) valoraciones: la lista "últimas 5 reseñas" del panel admin (ORDER BY created_at DESC LIMIT 5, sin filtro) ordenaba las
 *    150 mil filas (36 ms). Un índice simple en created_at la resuelve leyendo solo 5 filas.
 * 2) valoraciones: el AVG(puntaje) y el GROUP BY puntaje del panel admin (WHERE visible = 1) usaban
 *    (visible, created_at) y leían la tabla fila por fila. El índice (visible, puntaje) los resuelve solo con el índice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('remates', function (Blueprint $table) {
            $table->index('menu_id', 'remates_menu_id_idx');            // lo necesita la FK antes de quitar el compuesto
        });
        Schema::table('remates', function (Blueprint $table) {
            $table->dropIndex('remates_menu_activo_porciones_idx');
            $table->index(['activo', 'porciones', 'menu_id'], 'remates_activo_porciones_menu_idx');
        });
        Schema::table('valoraciones', function (Blueprint $table) {
            $table->index(['visible', 'puntaje'], 'valoraciones_visible_puntaje_idx');
            $table->index('created_at', 'valoraciones_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('valoraciones', function (Blueprint $table) {
            $table->dropIndex('valoraciones_visible_puntaje_idx');
            $table->dropIndex('valoraciones_created_at_idx');
        });
        Schema::table('remates', function (Blueprint $table) {
            $table->index(['menu_id', 'activo', 'porciones'], 'remates_menu_activo_porciones_idx'); // vuelve al estado anterior
        });
        Schema::table('remates', function (Blueprint $table) {
            $table->dropIndex('remates_activo_porciones_menu_idx');
            $table->dropIndex('remates_menu_id_idx');
        });
    }
};
