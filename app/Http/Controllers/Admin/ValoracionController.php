<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Valoracion;

/** Moderación de valoraciones: ocultar o eliminar las que incumplan las normas. */
class ValoracionController extends Controller
{
    /**
     * Lista paginada de valoraciones para moderarlas.
     * Ruta: GET /admin/valoraciones.
     */
    public function index()
    {
        $valoraciones = Valoracion::with(['local', 'user'])->latest()->paginate(12);

        return view('admin.valoraciones', compact('valoraciones'));
    }

    /**
     * Oculta o muestra una reseña. Ocultarla es una moderación reversible: el comentario sigue guardado
     * pero deja de verse y de contar en el puntaje del local.
     * Ruta: PATCH /admin/valoraciones/{valoracion}/alternar.
     */
    public function alternar(Valoracion $valoracion)
    {
        $valoracion->update(['visible' => ! $valoracion->visible]);

        return back()->with('exito', $valoracion->visible ? 'La reseña vuelve a verse.' : 'Ocultamos la reseña.');
    }

    /**
     * Elimina definitivamente una reseña que incumple las normas.
     * Ruta: DELETE /admin/valoraciones/{valoracion}.
     */
    public function destroy(Valoracion $valoracion)
    {
        $valoracion->delete();

        return back()->with('exito', 'Eliminamos la reseña.');
    }
}
