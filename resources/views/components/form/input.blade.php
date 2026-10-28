@props(['name', 'label', 'type' => 'text', 'value' => ''])

<div class="form-field">
    <label for="{{ $name }}">{{ $label }}</label>
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($type !== 'password') value="{{ $value }}" @endif
        aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
        @error($name) aria-describedby="erro-{{ $name }}" @enderror
        {{ $attributes->class(['form-input']) }}
    >
    @if ($type === 'password')
        <button type="button" class="password-toggle" data-password-toggle aria-controls="{{ $name }}" aria-pressed="false" hidden>Mostrar senha</button>
    @endif
    @error($name)
        <p id="erro-{{ $name }}" class="field-error">{{ $message }}</p>
    @enderror
</div>
