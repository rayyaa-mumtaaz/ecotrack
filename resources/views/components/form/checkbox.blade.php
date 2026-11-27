{{--
    Checkbox (misalnya status aktif). Nilai lama dipertahankan setelah validasi gagal.
    <x-form.checkbox name="is_active" label="Aktif" :checked="$category->is_active" />
--}}
@props(['name', 'label', 'checked' => false])

<label class="flex items-center gap-2 text-sm text-gray-700">
    <input type="checkbox" name="{{ $name }}" value="1"
        {{ $attributes->merge(['class' => 'rounded border-gray-300 text-emerald-600 focus:ring-emerald-500']) }}
        @checked(old('_token') ? old($name) : $checked)>
    {{ $label }}
</label>
