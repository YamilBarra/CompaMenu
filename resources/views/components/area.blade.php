{{--
    Componente <x-area>: cuadro de texto largo (textarea) con etiqueta, ayuda y error.
    Props: name, label, value, ayuda.
--}}
@props(['name', 'label', 'value' => null, 'ayuda' => null])
@php($id = $attributes->get('id', 'campo-'.str_replace(['.', '[', ']'], '-', $name)))
<div class="campo">
  <label for="{{ $id }}">{{ $label }}</label>
  <textarea id="{{ $id }}" name="{{ $name }}" {{ $attributes->except('id')->class(['form-control', 'invalido' => $errors->has($name)]) }}>{{ old($name, $value) }}</textarea>
  @if ($ayuda)<p class="ayuda">{{ $ayuda }}</p>@endif
  @error($name)<p class="error-campo">{{ $message }}</p>@enderror
</div>
