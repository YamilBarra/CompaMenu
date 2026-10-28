<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Local;
use Illuminate\Http\Request;

/** Base de los controladores del panel "Mi local": cada establecimiento solo toca los datos de su propio local. */
abstract class PanelController extends Controller
{
    /**
     * Devuelve el local del usuario autenticado. Si no tiene (por ejemplo, el administrador lo eliminó)
     * redirige al formulario de datos del local.
     */
    protected function local(Request $request): Local
    {
        $local = $request->user()->local;

        if (! $local) {
            abort(redirect()->route('panel.local.edit')->with('error', 'Primero registra los datos de tu local.'));
        }

        return $local;
    }
}
