<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Reseña de un estudiante sobre un local (1 a 5 estrellas y un comentario corto). */
class Valoracion extends Model
{
    protected $table = 'valoraciones';

    protected $fillable = [
        'local_id', 'user_id', 'puntaje', 'comentario',
        'calidad', 'porcion', 'precio', 'higiene', 'atencion', 'visible',
    ];

    /** Aspectos opcionales que el estudiante puede calificar. */
    public const ASPECTOS = [
        'calidad' => 'Calidad de la comida',
        'porcion' => 'Porción',
        'precio' => 'Relación calidad-precio',
        'higiene' => 'Higiene',
        'atencion' => 'Atención',
    ];

    /**
     * Conversión de tipos: visible como booleano.
     */
    protected function casts(): array
    {
        return ['visible' => 'boolean'];
    }

    /**
     * Local valorado (relación N:1).
     */
    public function local(): BelongsTo
    {
        return $this->belongsTo(Local::class);
    }

    /**
     * Estudiante autor de la reseña (relación N:1).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
