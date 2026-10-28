{{--
    Componente <x-selector>: lista desplegable con etiqueta y error de validación.
    Props: name, label, opciones (clave => texto), value y vacio (texto de la opción en blanco, opcional).
--}}
@props(['name', 'label', 'opciones', 'value' => null, 'vacio' => null])
@php($id = $attributes->get('id', 'campo-'.str_replace(['.', '[', ']'], '-', $name)))
<div class="campo">
  <label for="{{ $id }}">{{ $label }}</label>
  <select id="{{ $id }}" name="{{ $name }}" {{ $attributes->except('id')->class(['form-select', 'invalido' => $errors->has($name)]) }}>
    @if ($vacio !== null)<option value="">{{ $vacio }}</option>@endif
    @foreach ($opciones as $clave => $texto)
      <option value="{{ $clave }}" @selected((string) old($name, $value) === (string) $clave)>{{ $texto }}</option>
    @endforeach
  </select>
  @error($name)<p class="error-campo">{{ $message }}</p>@enderror
</div>
