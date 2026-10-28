<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Reserva de platos: el estudiante aparta porciones de un menú (o de un remate)
 * y las recoge en el local a la hora indicada.
 */
class Reserva extends Model
{
    protected $table = 'reservas';

    protected $fillable = [
        'codigo', 'user_id', 'local_id', 'menu_id', 'remate_id', 'cantidad',
        'precio_unitario', 'fecha_recojo', 'hora_recojo', 'nota', 'estado',
    ];

    public const ESTADOS = [
        'pendiente' => 'Pendiente',
        'confirmada' => 'Confirmada',
        'completada' => 'Entregada',
        'cancelada' => 'Cancelada',
        'rechazada' => 'Rechazada',
    ];

    /**
     * Conversión de tipos: precio_unitario con 2 decimales y fecha_recojo como fecha.
     */
    protected function casts(): array
    {
        return [
            'precio_unitario' => 'decimal:2',
            'fecha_recojo' => 'date',
        ];
    }

    /**
     * Nombre legible del estado ('completada' → 'Entregada').
     */
    public function getEstadoNombreAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? ucfirst($this->estado);
    }

    /**
     * Total a pagar en el local: precio unitario × cantidad.
     */
    public function getTotalAttribute(): float
    {
        return (float) $this->precio_unitario * $this->cantidad;
    }

    /** Una reserva "viva" todavía tiene porciones apartadas. */
    public function estaViva(): bool
    {
        return in_array($this->estado, ['pendiente', 'confirmada'], true);
    }

    /** Código corto para mostrar en el local, ej: K7M2QX */
    public static function nuevoCodigo(): string
    {
        do {
            $codigo = strtoupper(Str::random(6));
        } while (static::where('codigo', $codigo)->exists());

        return $codigo;
    }

    /** Devuelve las porciones apartadas (al cancelar o rechazar la reserva). */
    public function liberarPorciones(): void
    {
        if ($this->remate_id) {
            Remate::whereKey($this->remate_id)->increment('porciones', $this->cantidad);
        } else {
            Menu::whereKey($this->menu_id)->increment('porciones', $this->cantidad);
        }
    }

    /**
     * Estudiante que hizo la reserva (relación N:1).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Local donde se recoge el pedido (relación N:1).
     */
    public function local(): BelongsTo
    {
        return $this->belongsTo(Local::class);
    }

    /**
     * Menú reservado (relación N:1).
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Remate usado, si la reserva fue a precio de remate (relación N:1 opcional).
     */
    public function remate(): BelongsTo
    {
        return $this->belongsTo(Remate::class);
    }
}
