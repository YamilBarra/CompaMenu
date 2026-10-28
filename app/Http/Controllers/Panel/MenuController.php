<?php

namespace App\Http\Controllers\Panel;

use App\Http\Requests\MenuRequest;
use App\Models\Local;
use App\Models\Menu;
use App\Support\Texto;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** CRUD de menús del local (Crear, Leer, Actualizar y Eliminar) con foto y platos. */
class MenuController extends PanelController
{
    /** Leer: formulario (nuevo) + tabla con todos los menús del local. */
    public function index(Request $request)
    {
        $local = $this->local($request);

        return view('panel.menus', [
            'local' => $local,
            'menus' => $local->menus()->with('platos')->latest()->get(),
            'editar' => null,
        ]);
    }

    /** Actualizar (paso 1): la misma pantalla, pero con el formulario cargado con el menú elegido. */
    public function edit(Request $request, Menu $menu)
    {
        $local = $this->local($request);
        $menu = $this->propio($local, $menu)->load('platos');

        return view('panel.menus', [
            'local' => $local,
            'menus' => $local->menus()->with('platos')->latest()->get(),
            'editar' => $menu,
        ]);
    }

    /** Crear */
    public function store(MenuRequest $request)
    {
        $local = $this->local($request);

        DB::transaction(function () use ($request, $local) {
            $menu = $local->menus()->create($this->datos($request) + ['foto' => $this->guardarFoto($request->file('foto'))]);
            $this->guardarPlatos($menu, $request->validated('platos', []));
        });

        return redirect()->route('panel.menus.index')->with('exito', 'Agregamos el menú. Ya se ve en la página de inicio.');
    }

    /** Actualizar (paso 2) */
    public function update(MenuRequest $request, Menu $menu)
    {
        $local = $this->local($request);
        $menu = $this->propio($local, $menu);

        DB::transaction(function () use ($request, $menu) {
            $foto = $menu->foto;
            if ($request->hasFile('foto')) {
                $this->borrarFotoSubida($menu->foto);
                $foto = $this->guardarFoto($request->file('foto'));
            } elseif ($request->boolean('quitar_foto')) {
                $this->borrarFotoSubida($menu->foto);
                $foto = null;
            }

            $menu->update($this->datos($request) + ['foto' => $foto]);
            $this->guardarPlatos($menu, $request->validated('platos', []));
        });

        return redirect()->route('panel.menus.index')->with('exito', 'Guardamos los cambios del menú.');
    }

    /** Cambia rápido entre disponible y agotado. */
    public function disponibilidad(Request $request, Menu $menu)
    {
        $menu = $this->propio($this->local($request), $menu);
        $menu->update(['disponible' => ! $menu->disponible]);

        return back()->with('exito', $menu->disponible ? 'El menú vuelve a estar disponible.' : 'Marcamos el menú como agotado.');
    }

    /** Eliminar (el navegador pide confirmación antes de enviar). */
    public function destroy(Request $request, Menu $menu)
    {
        $menu = $this->propio($this->local($request), $menu);

        if ($menu->reservas()->whereIn('estado', ['pendiente', 'confirmada'])->exists()) {
            return back()->with('error', 'Este menú tiene reservas pendientes. Atiéndelas antes de eliminarlo.');
        }

        $this->borrarFotoSubida($menu->foto);
        $menu->delete();

        return redirect()->route('panel.menus.index')->with('exito', 'Eliminamos el menú.');
    }

    // ---------- Apoyo ----------

    /** Solo se puede tocar un menú del propio local (si no, 404). */
    private function propio(Local $local, Menu $menu): Menu
    {
        return $local->menus()->findOrFail($menu->id);
    }

    /**
     * Arma las columnas del menú a partir de los datos ya validados,
     * limpiando el texto libre con Texto::limpio (quita etiquetas HTML).
     */
    private function datos(MenuRequest $request): array
    {
        $v = $request->validated();

        return [
            'nombre' => Texto::limpio($v['nombre']),
            'descripcion' => Texto::limpio($v['descripcion'] ?? null),
            'precio' => $v['precio'],
            'tipo' => $v['tipo'],
            'acompanamiento' => Texto::limpio($v['acompanamiento'] ?? null),
            'bebida' => Texto::limpio($v['bebida'] ?? null),
            'fecha' => $v['fecha'] ?? null,
            'hora_inicio' => $v['hora_inicio'] ?? null,
            'hora_fin' => $v['hora_fin'] ?? null,
            'porciones' => $v['porciones'],
            'disponible' => (bool) ($v['disponible'] ?? false),
        ];
    }

    /** Los platos se reemplazan completos: se borran los anteriores y se crean los del formulario. */
    private function guardarPlatos(Menu $menu, array $platos): void
    {
        $menu->platos()->delete();

        foreach (array_values($platos) as $i => $plato) {
            $menu->platos()->create([
                'nombre' => Texto::limpio($plato['nombre']),
                'categoria' => $plato['categoria'],
                'descripcion' => Texto::limpio($plato['descripcion'] ?? null),
                'orden' => $i,
            ]);
        }
    }

    /** La foto llega en $request->file('foto') (en PHP puro sería $_FILES["foto"]). Se guarda en /public/uploads/menus. */
    private function guardarFoto(?UploadedFile $archivo): ?string
    {
        if (! $archivo) {
            return null;
        }

        $nombre = Str::uuid().'.'.($archivo->guessExtension() ?: 'jpg');
        $archivo->move(public_path('uploads/menus'), $nombre);

        return 'uploads/menus/'.$nombre;
    }

    /** Solo borra fotos subidas por los locales, nunca las fotos de ejemplo de /public/img. */
    private function borrarFotoSubida(?string $ruta): void
    {
        if ($ruta && str_starts_with($ruta, 'uploads/menus/')) {
            $archivo = public_path($ruta);
            if (is_file($archivo)) {
                @unlink($archivo);
            }
        }
    }
}
