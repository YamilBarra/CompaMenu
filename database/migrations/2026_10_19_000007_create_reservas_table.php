<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reservas de platos (sección 5.6 del documento: "un sistema de reservas de platos").
 * El estudiante aparta porciones de un menú (o de un remate) y las recoge en el local.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            // Código corto (ej. K7M2QX) que el estudiante muestra en el local para recoger su pedido
            $table->string('codigo', 8)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('local_id')->constrained('locales')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->foreignId('remate_id')->nullable()->constrained('remates')->nullOnDelete();
            $table->unsignedTinyInteger('cantidad')->default(1);
            // Se guarda el precio al reservar: si el local cambia después el precio del menú, la reserva no se altera
            $table->decimal('precio_unitario', 5, 2);
            $table->date('fecha_recojo');
            $table->time('hora_recojo');
            $table->string('nota', 120)->nullable();
            $table->enum('estado', ['pendiente', 'confirmada', 'completada', 'cancelada', 'rechazada'])->default('pendiente');
            $table->timestamps();

            // Índices: el panel del local filtra por (local, estado) y el estudiante por (usuario, estado)
            $table->index(['local_id', 'estado']);
            $table->index(['user_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
