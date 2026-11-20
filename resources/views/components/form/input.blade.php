{{--
    Input form + label + pesan error validasi.
    <x-form.input name="email" label="Email" type="email" required />
--}}
@props(['name', 'label', 'type' => 'text', 'value' => null])

<div>
    <label for="{{ $name }}" class="mb-1 block text-sm font-medium text-gray-700">
        {{ $label }}
        @if ($attributes->has('required'))
            <span class="text-red-500">*</span>
        @endif
    </label>

    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
        @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
        {{ $attributes->class([
            'block w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2',
            'border-red-400 focus:border-red-500 focus:ring-red-200' => $errors->has($name),
            'border-gray-300 focus:border-emerald-500 focus:ring-emerald-200' => ! $errors->has($name),
        ]) }}>

    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
