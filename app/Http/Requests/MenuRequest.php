<?php

namespace App\Http\Requests;

use App\Models\Menu;
use App\Models\Plato;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validaciones del formulario de menús (panel "Mi local"). */
class MenuRequest extends FormRequest
{
    /**
     * Solo un usuario con rol establecimiento puede enviar este formulario.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->esEstablecimiento();
    }

    /**
     * Antes de validar: descarta las filas de platos vacías y convierte los checkboxes
     * (que no se envían cuando están desmarcados) a booleanos.
     */
    protected function prepareForValidation(): void
    {
        // Se ignoran las filas de platos que el usuario dejó vacías
        $platos = collect($this->input('platos', []))
            ->filter(fn ($p) => is_array($p) && trim((string) ($p['nombre'] ?? '')) !== '')
            ->values()
            ->all();

        $this->merge([
            'platos' => $platos,
            'disponible' => $this->boolean('disponible'),
            'quitar_foto' => $this->boolean('quitar_foto'),
        ]);
    }

    /**
     * Reglas de validación del menú: se revisa cada campo antes de guardarlo.
     * La foto solo acepta JPG, PNG o WEBP de hasta 2 MB (evita subir archivos peligrosos).
     * @see https://laravel.com/docs/validation#form-request-validation
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'min:3', 'max:80'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'precio' => ['required', 'numeric', 'between:1,30'],
            'tipo' => ['required', Rule::in(array_keys(Menu::TIPOS))],
            'acompanamiento' => ['nullable', 'string', 'max:80'],
            'bebida' => ['nullable', 'string', 'max:60'],
            'fecha' => ['nullable', 'date'],
            'hora_inicio' => ['nullable', 'required_with:hora_fin', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'date_format:H:i', 'after:hora_inicio'],
            'porciones' => ['required', 'integer', 'between:0,200'],
            'disponible' => ['boolean'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'quitar_foto' => ['boolean'],
            'platos' => ['array', 'max:6'],
            'platos.*.nombre' => ['required', 'string', 'max:80'],
            'platos.*.categoria' => ['required', Rule::in(array_keys(Plato::CATEGORIAS))],
            'platos.*.descripcion' => ['nullable', 'string', 'max:150'],
        ];
    }

    /**
     * Mensajes de error personalizados en español.
     */
    public function messages(): array
    {
        return [
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
            'precio.between' => 'El precio debe estar entre S/. 1 y S/. 30.',
            'foto.max' => 'La foto no puede pesar más de 2 MB.',
            'foto.image' => 'El archivo debe ser una imagen (JPG, PNG o WEBP).',
        ];
    }
}
