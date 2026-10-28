<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CompaMenu: la tabla "users" de Laravel hace de tabla USUARIOS del documento (sección 5.3).
 * Le agregamos el rol (Estudiante, Establecimiento o Administrador), un teléfono y un estado activo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rol del usuario. estudiante: reserva y opina · establecimiento: administra su local ·
            // administrador: modera y consulta métricas. Es un enum para que la base rechace valores inventados.
            $table->enum('rol', ['estudiante', 'establecimiento', 'administrador'])
                ->default('estudiante')
                ->after('password');
            $table->string('telefono', 20)->nullable()->after('rol');
            // false = cuenta desactivada por el administrador (no puede ingresar; ver VerificarRol)
            $table->boolean('activo')->default(true)->after('telefono');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rol', 'telefono', 'activo']);
        });
    }
};
