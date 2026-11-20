{{-- Menampilkan pesan dari session: ->with('success', '...') atau ->with('error', '...') --}}
@foreach (['success' => 'check', 'error' => 'alert'] as $type => $icon)
    @if (session($type))
        <div data-alert="{{ $type }}"
            @class([
                'mb-4 flex items-start gap-3 rounded-lg border px-4 py-3 text-sm',
                'border-green-200 bg-green-50 text-green-800' => $type === 'success',
                'border-red-200 bg-red-50 text-red-800' => $type === 'error',
            ])>
            <x-icon :name="$icon" class="mt-0.5 h-5 w-5 shrink-0" />
            <p class="flex-1">{{ session($type) }}</p>
            <button type="button" data-alert-close class="opacity-60 hover:opacity-100" aria-label="Tutup">
                <x-icon name="x" class="h-4 w-4" />
            </button>
        </div>
    @endif
@endforeach
