<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Locales: pensiones, quioscos y comedores. Cada usuario "establecimiento" administra un solo local. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locales', function (Blueprint $table) {
            $table->id();
            // unique(): cada usuario administra un solo local (relación 1:1).
            // cascadeOnDelete(): si se elimina el usuario, se elimina su local.
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nombre', 60);
            $table->enum('tipo_local', ['pension', 'kiosko', 'comedor'])->default('comedor');
            $table->string('direccion', 120)->nullable();
            $table->unsignedSmallInteger('distancia_campus')->default(0); // en metros
            $table->string('horario', 80)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locales');
    }
};
