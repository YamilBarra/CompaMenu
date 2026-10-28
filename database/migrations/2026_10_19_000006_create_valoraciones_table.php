<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Valoraciones (reseñas) de los estudiantes sobre un local. Una por estudiante y local. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valoraciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('local_id')->constrained('locales')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('puntaje'); // 1 a 5 estrellas
            $table->string('comentario', 150);
            // Aspectos opcionales (sección 6.1 g del documento), de 1 a 5
            $table->unsignedTinyInteger('calidad')->nullable();
            $table->unsignedTinyInteger('porcion')->nullable();
            $table->unsignedTinyInteger('precio')->nullable();
            $table->unsignedTinyInteger('higiene')->nullable();
            $table->unsignedTinyInteger('atencion')->nullable();
            $table->boolean('visible')->default(true); // el administrador puede ocultarla (moderación)
            $table->timestamps();

            // Restricción única: un estudiante solo puede tener una reseña por local
            // (si vuelve a opinar, ResenaController la actualiza con updateOrCreate).
            $table->unique(['local_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valoraciones');
    }
};
