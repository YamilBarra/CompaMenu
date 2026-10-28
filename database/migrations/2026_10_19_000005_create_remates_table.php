<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Remate Solidario: un menú a precio rebajado en las últimas horas de atención. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('local_id')->constrained('locales')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->decimal('precio_remate', 5, 2);
            $table->unsignedSmallInteger('porciones')->default(1);
            $table->time('hora_inicio');
            $table->time('hora_fin')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Índice para la página pública de remates (activos, ordenados por hora de inicio)
            $table->index(['activo', 'hora_inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remates');
    }
};
