<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Menús que publica cada local (lo que se ve en las tarjetas de Inicio). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('local_id')->constrained('locales')->cascadeOnDelete();
            $table->string('nombre', 80);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 5, 2); // hasta 999.99; el formulario limita el precio de S/. 1 a S/. 30
            $table->enum('tipo', ['criollo', 'andino', 'vegetariano', 'proteico'])->default('criollo');
            $table->string('acompanamiento', 80)->nullable();
            $table->string('bebida', 60)->nullable();
            $table->date('fecha')->nullable();
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->unsignedSmallInteger('porciones')->default(0); // porciones que quedan (sirven para reservar)
            $table->boolean('disponible')->default(true);
            $table->string('foto')->nullable(); // ruta dentro de /public (ej: img/ceviche.jpg o uploads/menus/xxx.jpg)
            $table->timestamps();

            // Índices: aceleran el buscador, que filtra por disponibilidad y precio, y por tipo de plato.
            $table->index(['disponible', 'precio']);
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
