<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Platos que forman parte de un menú (entrada, segundo, postre...). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->string('nombre', 80);
            $table->enum('categoria', ['entrada', 'segundo', 'postre', 'extra'])->default('segundo');
            $table->string('descripcion', 150)->nullable();
            $table->unsignedTinyInteger('orden')->default(0); // posición del plato dentro del menú (0 = primero)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platos');
    }
};
