<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Usuario de CompaMenu. El campo "rol" decide qué puede hacer:
 * estudiante, establecimiento o administrador (sección 6.3 del documento).
 */
#[Fillable(['name', 'email', 'password', 'rol', 'telefono', 'activo'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROL_ESTUDIANTE = 'estudiante';
    public const ROL_ESTABLECIMIENTO = 'establecimiento';
    public const ROL_ADMIN = 'administrador';

    /**
     * Conversión de tipos: email_verified_at como fecha, activo como booleano y password con 'hashed',
     * que guarda siempre la contraseña con bcrypt al asignarla (nunca en texto plano).
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // Laravel guarda la contraseña con hash (equivale a password_hash)
            'activo' => 'boolean',
        ];
    }

    /**
     * ¿Es estudiante? Atajo para usar en vistas y controladores en vez de comparar el texto del rol.
     */
    public function esEstudiante(): bool
    {
        return $this->rol === self::ROL_ESTUDIANTE;
    }

    /**
     * ¿Es dueño de un local?
     */
    public function esEstablecimiento(): bool
    {
        return $this->rol === self::ROL_ESTABLECIMIENTO;
    }

    /**
     * ¿Es administrador?
     */
    public function esAdmin(): bool
    {
        return $this->rol === self::ROL_ADMIN;
    }

    /**
     * Nombre legible del rol. Se usa como $usuario->rol_nombre.
     */
    public function getRolNombreAttribute(): string
    {
        return match ($this->rol) {
            self::ROL_ESTABLECIMIENTO => 'Establecimiento',
            self::ROL_ADMIN => 'Administrador',
            default => 'Estudiante',
        };
    }

    /**
     * Local que administra el usuario si es establecimiento (relación 1:1).
     */
    public function local(): HasOne
    {
        return $this->hasOne(Local::class);
    }

    /**
     * Reseñas escritas por el estudiante (relación 1:N).
     */
    public function valoraciones(): HasMany
    {
        return $this->hasMany(Valoracion::class);
    }

    /**
     * Reservas hechas por el estudiante (relación 1:N).
     */
    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }
}
