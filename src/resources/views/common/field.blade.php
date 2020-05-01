@php
$classes = ['input'];

if ($errors->has($name)) {
$classes[] = 'invalid';
}

if (empty($value)) {
$classes[] = 'empty';
}

$class = implode(' ', $classes);
@endphp

<input type="{{ $type }}" class="{{ $class }}" name="{{ $name }}" placeholder="{{ $label }}" value="{{ $value }}" {!! $attributes !!} />

<span class="input-icon"></span>
<span class="input-label">{{ $label }}</span>
@spaceless
<span class="input-error">
  @error($name)
  {{ $message }}
  @enderror
</span>
@endspaceless
