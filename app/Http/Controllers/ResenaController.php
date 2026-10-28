<?php

namespace App\Http\Controllers;

use App\Models\Local;
use App\Models\Valoracion;
use App\Support\Texto;
use Illuminate\Http\Request;

/** Reseñas: todos las leen; solo los estudiantes con sesión iniciada pueden publicar. */
class ResenaController extends Controller
{
    /**
     * Lista pública de reseñas (solo las visibles), con filtro por local y el formulario para estudiantes.
     * Si el estudiante ya reseñó el local elegido, el formulario se llena con su reseña para editarla.
     * Ruta: GET /resenas.
     */
    public function index(Request $request)
    {
        $localId = (int) $request->query('local', 0);

        $resenas = Valoracion::query()
            ->where('visible', true)
            ->with(['local', 'user'])
            ->when($localId > 0, fn ($q) => $q->where('local_id', $localId))
            ->latest()
            ->paginate(8)
            ->withQueryString();

        $locales = Local::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);

        // Si el estudiante ya reseñó el local elegido, el formulario carga su reseña para editarla
        $miResena = null;
        $usuario = $request->user();
        if ($usuario && $usuario->esEstudiante() && $localId > 0) {
            $miResena = Valoracion::where('user_id', $usuario->id)->where('local_id', $localId)->first();
        }

        return view('resenas.index', compact('resenas', 'locales', 'localId', 'miResena'));
    }

    /**
     * Crea o actualiza la reseña del estudiante para un local. updateOrCreate sobre (local_id, user_id)
     * asegura una reseña por estudiante y local. El comentario se limpia de HTML.
     * Ruta: POST /resenas · Rol: estudiante.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'local_id' => ['required', 'integer', 'exists:locales,id'],
            'puntaje' => ['required', 'integer', 'between:1,5'],
            'comentario' => ['required', 'string', 'min:5', 'max:150'],
            'calidad' => ['nullable', 'integer', 'between:1,5'],
            'porcion' => ['nullable', 'integer', 'between:1,5'],
            'precio' => ['nullable', 'integer', 'between:1,5'],
            'higiene' => ['nullable', 'integer', 'between:1,5'],
            'atencion' => ['nullable', 'integer', 'between:1,5'],
        ]);

        $datos['comentario'] = Texto::limpio($datos['comentario']);
        if ($datos['comentario'] === null || mb_strlen($datos['comentario']) < 5) {
            return back()->withInput()->withErrors(['comentario' => 'Escribe un comentario de al menos 5 letras.']);
        }

        $local = Local::where('activo', true)->findOrFail($datos['local_id']);

        // Una reseña por estudiante y local: si ya existe, se actualiza
        $valoracion = Valoracion::updateOrCreate(
            ['local_id' => $local->id, 'user_id' => $request->user()->id],
            $datos + ['visible' => true],
        );

        $mensaje = $valoracion->wasRecentlyCreated ? 'Publicamos tu reseña. ¡Gracias por ayudar a otros estudiantes!' : 'Actualizamos tu reseña.';

        return redirect()->route('resenas.index', ['local' => $local->id])->with('exito', $mensaje);
    }

    /**
     * Elimina una reseña. Solo puede hacerlo su autor o un administrador (si no, 403).
     * Ruta: DELETE /resenas/{valoracion}.
     */
    public function destroy(Request $request, Valoracion $valoracion)
    {
        $usuario = $request->user();
        abort_unless($usuario->esAdmin() || $valoracion->user_id === $usuario->id, 403);

        $valoracion->delete();

        return back()->with('exito', 'Se retiró la reseña.');
    }
}
