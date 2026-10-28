{{--
    Componente <x-campo>: campo de texto con etiqueta, ayuda y mensaje de error de validación.
    Props: name, label, type, value, ayuda. old() conserva lo escrito si el formulario falla.
    Las contraseñas nunca se vuelven a mostrar (no se imprime value).
--}}
@props(['name', 'label', 'type' => 'text', 'value' => null, 'ayuda' => null])
@php($id = $attributes->get('id', 'campo-'.str_replace(['.', '[', ']'], '-', $name)))
<div class="campo">
  <label for="{{ $id }}">{{ $label }}</label>
  <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}" @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
    {{ $attributes->except('id')->class(['form-control', 'invalido' => $errors->has($name)]) }}>
  @if ($ayuda)<p class="ayuda">{{ $ayuda }}</p>@endif
  @error($name)<p class="error-campo">{{ $message }}</p>@enderror
</div>
