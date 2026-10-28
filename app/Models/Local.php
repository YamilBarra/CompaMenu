<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Pensión, quiosco o comedor cercano al campus. */
class Local extends Model
{
    protected $table = 'locales';

    protected $fillable = [
        'user_id', 'nombre', 'tipo_local', 'direccion', 'distancia_campus',
        'horario', 'telefono', 'descripcion', 'activo',
    ];

    public const TIPOS = [
        'pension' => 'Pensión',
        'kiosko' => 'Quiosco',
        'comedor' => 'Comedor',
    ];

    /**
     * Conversión automática de tipos: activo se lee como booleano.
     */
    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    /**
     * Nombre legible del tipo ('pension' → 'Pensión'). Se usa como $local->tipo_nombre.
     */
    public function getTipoNombreAttribute(): string
    {
        return self::TIPOS[$this->tipo_local] ?? 'Local';
    }

    /**
     * Dueño del local: usuario con rol establecimiento (relación N:1).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Menús que publica el local (relación 1:N).
     */
    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    /**
     * Remates del local (relación 1:N).
     */
    public function remates(): HasMany
    {
        return $this->hasMany(Remate::class);
    }

    /**
     * Todas las valoraciones del local, visibles u ocultas (relación 1:N).
     */
    public function valoraciones(): HasMany
    {
        return $this->hasMany(Valoracion::class);
    }

    /** Solo las valoraciones que el administrador no ocultó. */
    public function valoracionesVisibles(): HasMany
    {
        return $this->hasMany(Valoracion::class)->where('visible', true);
    }

    /**
     * Reservas que recibió el local (relación 1:N).
     */
    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    /** Agrega "puntaje" (promedio) y "resenas_count" a la consulta. */
    public function scopeConPuntaje($query)
    {
        return $query
            ->withAvg('valoracionesVisibles as puntaje', 'puntaje')
            ->withCount('valoracionesVisibles as resenas_count');
    }
}
