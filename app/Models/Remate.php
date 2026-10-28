<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Remate Solidario: menú a precio rebajado en las últimas horas de atención. */
class Remate extends Model
{
    protected $table = 'remates';

    protected $fillable = ['local_id', 'menu_id', 'precio_remate', 'porciones', 'hora_inicio', 'hora_fin', 'activo'];

    /**
     * Conversión de tipos: precio_remate con 2 decimales y activo como booleano.
     */
    protected function casts(): array
    {
        return [
            'precio_remate' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    /** Remates que se pueden ver y reservar: activos, con porciones y con el menú disponible. */
    public function scopeVigentes($query)
    {
        return $query->where('activo', true)
            ->where('porciones', '>', 0)
            ->whereHas('menu', fn ($q) => $q->where('disponible', true))
            ->whereHas('local', fn ($q) => $q->where('activo', true));
    }

    /**
     * Horario en texto, por ejemplo 'Desde las 3:00 pm hasta las 5:00 pm'.
     */
    public function getHoraTextoAttribute(): string
    {
        $texto = 'Desde las '.date('g:i a', strtotime($this->hora_inicio));
        if ($this->hora_fin) {
            $texto .= ' hasta las '.date('g:i a', strtotime($this->hora_fin));
        }

        return $texto;
    }

    /**
     * Local que publica el remate (relación N:1).
     */
    public function local(): BelongsTo
    {
        return $this->belongsTo(Local::class);
    }

    /**
     * Menú que se está rematando (relación N:1).
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Reservas hechas a precio de remate (relación 1:N).
     */
    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }
}
