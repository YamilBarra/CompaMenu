<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** Menú que publica un local (precio, tipo, foto, porciones). Tiene varios platos. */
class Menu extends Model
{
    protected $table = 'menus';

    protected $fillable = [
        'local_id', 'nombre', 'descripcion', 'precio', 'tipo', 'acompanamiento', 'bebida',
        'fecha', 'hora_inicio', 'hora_fin', 'porciones', 'disponible', 'foto',
    ];

    public const TIPOS = [
        'criollo' => 'Criollo',
        'andino' => 'Andino',
        'vegetariano' => 'Vegetariano',
        'proteico' => 'Alto en proteínas',
    ];

    /**
     * Conversión de tipos: precio con 2 decimales, fecha como fecha y disponible como booleano.
     */
    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'fecha' => 'date',
            'disponible' => 'boolean',
        ];
    }

    /**
     * Nombre legible del tipo ('proteico' → 'Alto en proteínas'). Se usa como $menu->tipo_nombre.
     */
    public function getTipoNombreAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? ucfirst($this->tipo);
    }

    /** Dirección completa de la foto; si el menú no tiene foto devuelve null. */
    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? asset($this->foto) : null;
    }

    /**
     * Horario en texto, por ejemplo '11:30 a 15:30'. Se usa como $menu->horario.
     */
    public function getHorarioAttribute(): ?string
    {
        if (! $this->hora_inicio) {
            return null;
        }
        $texto = substr($this->hora_inicio, 0, 5);
        if ($this->hora_fin) {
            $texto .= ' a '.substr($this->hora_fin, 0, 5);
        }

        return $texto;
    }

    /**
     * Scope local: Menu::disponibles() deja solo los menús marcados como disponibles.
     * @see https://laravel.com/docs/eloquent#local-scopes
     */
    public function scopeDisponibles($query)
    {
        return $query->where('disponible', true);
    }

    /**
     * Local que publica el menú (relación N:1).
     */
    public function local(): BelongsTo
    {
        return $this->belongsTo(Local::class);
    }

    /**
     * Platos que forman el menú, ordenados por la columna 'orden' (relación 1:N).
     */
    public function platos(): HasMany
    {
        return $this->hasMany(Plato::class)->orderBy('orden');
    }

    /**
     * Remates que se hicieron sobre este menú (relación 1:N).
     */
    public function remates(): HasMany
    {
        return $this->hasMany(Remate::class);
    }

    /** Remate activo del menú (si tiene uno con porciones). */
    public function remateActivo(): HasOne
    {
        return $this->hasOne(Remate::class)->where('activo', true)->where('porciones', '>', 0)->latestOfMany();
    }

    /**
     * Reservas de este menú (relación 1:N).
     */
    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }
}
