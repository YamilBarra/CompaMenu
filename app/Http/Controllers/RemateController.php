<?php

namespace App\Http\Controllers;

use App\Models\Remate;

/** Remate Solidario (público): platos rebajados por la tarde. */
class RemateController extends Controller
{
    /**
     * Remate Solidario público: remates vigentes (activos, con porciones y con el menú disponible)
     * ordenados por hora de inicio.
     * Ruta: GET /remates.
     */
    public function index()
    {
        $remates = Remate::vigentes()
            ->with(['menu', 'local'])
            ->orderBy('hora_inicio')
            ->get();

        return view('remates.index', compact('remates'));
    }
}
