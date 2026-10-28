<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Plato que forma parte de un menú (entrada, segundo, postre...). */
class Plato extends Model
{
    protected $table = 'platos';

    protected $fillable = ['menu_id', 'nombre', 'categoria', 'descripcion', 'orden'];

    public const CATEGORIAS = [
        'entrada' => 'Entrada',
        'segundo' => 'Segundo',
        'postre' => 'Postre',
        'extra' => 'Extra',
    ];

    /**
     * Nombre legible de la categoría ('segundo' → 'Segundo').
     */
    public function getCategoriaNombreAttribute(): string
    {
        return self::CATEGORIAS[$this->categoria] ?? ucfirst($this->categoria);
    }

    /**
     * Menú al que pertenece el plato (relación N:1).
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
