<?php

namespace App\Http\Controllers\Panel;

use App\Models\Local;
use App\Support\Texto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Datos del local: nombre, ubicación, horario y contacto. */
class LocalController extends PanelController
{
    /**
     * Formulario con los datos del local del usuario (o vacío si el administrador lo borró).
     * Ruta: GET /mi-local/datos (panel.local.edit).
     */
    public function edit(Request $request)
    {
        return view('panel.local', ['local' => $request->user()->local]);
    }

    /**
     * Guarda los datos del local. Usa updateOrCreate con el user_id de la sesión: el id del local nunca viene
     * del formulario, así que nadie puede editar el local de otro.
     * Ruta: PUT /mi-local/datos.
     */
    public function update(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'min:3', 'max:60'],
            'tipo_local' => ['required', Rule::in(array_keys(Local::TIPOS))],
            'direccion' => ['nullable', 'string', 'max:120'],
            'distancia_campus' => ['required', 'integer', 'between:0,5000'],
            'horario' => ['nullable', 'string', 'max:80'],
            'telefono' => ['nullable', 'regex:/^[0-9+\s-]{6,20}$/'],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ]);

        foreach (['nombre', 'direccion', 'horario', 'descripcion'] as $campo) {
            $datos[$campo] = Texto::limpio($datos[$campo] ?? null);
        }

        // Si el administrador borró el local, esta pantalla lo vuelve a crear
        Local::updateOrCreate(['user_id' => $request->user()->id], $datos + ['activo' => true]);

        return redirect()->route('panel.local.edit')->with('exito', 'Guardamos los datos de tu local.');
    }
}
