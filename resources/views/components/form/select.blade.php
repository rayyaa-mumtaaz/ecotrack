{{--
    Select form + label + pesan error validasi.
    <x-form.select name="role" label="Role" :options="App\Models\User::ROLES" required />
    $options berupa array [nilai => label].
--}}
@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => '-- Pilih --'])

<div>
    <label for="{{ $name }}" class="mb-1 block text-sm font-medium text-gray-700">
        {{ $label }}
        @if ($attributes->has('required'))
            <span class="text-red-500">*</span>
        @endif
    </label>

    <select id="{{ $name }}" name="{{ $name }}"
        {{ $attributes->class([
            'block w-full rounded-lg border bg-white px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2',
            'border-red-400 focus:border-red-500 focus:ring-red-200' => $errors->has($name),
            'border-gray-300 focus:border-emerald-500 focus:ring-emerald-200' => ! $errors->has($name),
        ]) }}>
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) old($name, $value) === (string) $optionValue)>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>

    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
