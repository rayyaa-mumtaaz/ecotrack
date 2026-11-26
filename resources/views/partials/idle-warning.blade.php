{{-- Peringatan sebelum logout otomatis. Dikendalikan jQuery di resources/js/app.js --}}
<div id="idle-warning" class="fixed inset-x-4 bottom-4 z-50 hidden sm:left-auto sm:right-6 sm:w-96" role="alert">
    <div class="rounded-xl border border-amber-200 bg-white p-4 shadow-lg">
        <div class="flex items-start gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                <x-icon name="alert" class="h-5 w-5" />
            </span>
            <div class="flex-1">
                <p class="font-semibold text-gray-900">Sesi hampir berakhir</p>
                <p class="mt-1 text-sm text-gray-600">
                    Anda akan logout otomatis dalam <span id="idle-countdown" class="font-semibold text-amber-700">60</span> detik karena tidak ada aktivitas.
                </p>
                <button type="button" id="idle-stay" class="mt-3 rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-emerald-700">
                    Tetap masuk
                </button>
            </div>
        </div>
    </div>
</div>
