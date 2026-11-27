{{--
    Textarea + label + pesan error validasi.
    <x-form.textarea name="description" label="Deskripsi" :value="$category->description" />
--}}
@props(['name', 'label', 'value' => null, 'rows' => 3])

<div>
    <label for="{{ $name }}" class="mb-1 block text-sm font-medium text-gray-700">
        {{ $label }}
        @if ($attributes->get('required'))
            <span class="text-red-500">*</span>
        @endif
    </label>

    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}"
        {{ $attributes->class([
            'block w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2',
            'border-red-400 focus:border-red-500 focus:ring-red-200' => $errors->has($name),
            'border-gray-300 focus:border-emerald-500 focus:ring-emerald-200' => ! $errors->has($name),
        ]) }}>{{ old($name, $value) }}</textarea>

    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
