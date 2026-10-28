<?php

namespace App\Http\Controllers\Panel;

use App\Models\Menu;
use App\Models\Remate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Remate Solidario desde el panel: el local publica, edita y retira sus ofertas de la tarde. */
class RemateController extends PanelController
{
    /**
     * Formulario + tabla con los remates del local.
     * Ruta: GET /mi-local/remates.
     */
    public function index(Request $request)
    {
        $local = $this->local($request);

        return view('panel.remates', [
            'local' => $local,
            'remates' => $local->remates()->with('menu')->latest()->get(),
            'menus' => $local->menus()->orderBy('nombre')->get(),
            'editar' => null,
        ]);
    }

    /**
     * Igual que index, con el formulario cargado con el remate elegido.
     * Se busca dentro de los remates del propio local (404 si es de otro).
     * Ruta: GET /mi-local/remates/{remate}/editar.
     */
    public function edit(Request $request, Remate $remate)
    {
        $local = $this->local($request);
        $remate = $local->remates()->findOrFail($remate->id);

        return view('panel.remates', [
            'local' => $local,
            'remates' => $local->remates()->with('menu')->latest()->get(),
            'menus' => $local->menus()->orderBy('nombre')->get(),
            'editar' => $remate,
        ]);
    }

    /**
     * Publica un remate nuevo.
     * Ruta: POST /mi-local/remates.
     */
    public function store(Request $request)
    {
        $local = $this->local($request);
        $datos = $this->validar($request, $local->id);

        $local->remates()->create($datos);

        return redirect()->route('panel.remates.index')->with('exito', 'Publicamos el remate. Los estudiantes ya lo ven.');
    }

    /**
     * Actualiza un remate del propio local.
     * Ruta: PUT /mi-local/remates/{remate}.
     */
    public function update(Request $request, Remate $remate)
    {
        $local = $this->local($request);
        $remate = $local->remates()->findOrFail($remate->id);

        $remate->update($this->validar($request, $local->id));

        return redirect()->route('panel.remates.index')->with('exito', 'Guardamos los cambios del remate.');
    }

    /**
     * Retira un remate. No se permite si tiene reservas pendientes o confirmadas.
     * Ruta: DELETE /mi-local/remates/{remate}.
     */
    public function destroy(Request $request, Remate $remate)
    {
        $remate = $this->local($request)->remates()->findOrFail($remate->id);

        if ($remate->reservas()->whereIn('estado', ['pendiente', 'confirmada'])->exists()) {
            return back()->with('error', 'Este remate tiene reservas pendientes. Atiéndelas antes de retirarlo.');
        }

        $remate->delete();

        return redirect()->route('panel.remates.index')->with('exito', 'Retiramos el remate.');
    }

    /**
     * Valida el formulario de remates (compartido por crear y editar).
     * Reglas clave: el menú debe pertenecer al local (Rule::exists con where local_id)
     * y el precio de remate debe ser menor que el precio normal del menú.
     */
    private function validar(Request $request, int $localId): array
    {
        $request->merge(['activo' => $request->boolean('activo')]);

        $datos = $request->validate([
            // Solo se puede rematar un menú del propio local
            'menu_id' => ['required', 'integer', Rule::exists('menus', 'id')->where('local_id', $localId)],
            'precio_remate' => ['required', 'numeric', 'between:0.5,30'],
            'porciones' => ['required', 'integer', 'between:1,200'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'date_format:H:i', 'after:hora_inicio'],
            'activo' => ['boolean'],
        ], [
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ]);

        $menu = Menu::findOrFail($datos['menu_id']);
        if ((float) $datos['precio_remate'] >= (float) $menu->precio) {
            throw ValidationException::withMessages([
                'precio_remate' => 'El precio de remate debe ser menor que el precio del menú (S/. '.number_format($menu->precio, 2).').',
            ]);
        }

        return $datos + ['local_id' => $localId];
    }
}
