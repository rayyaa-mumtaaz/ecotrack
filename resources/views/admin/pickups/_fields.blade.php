{{-- Field data pengangkutan, dipakai form tambah dan ubah --}}
<div class="grid gap-4 md:grid-cols-2">
    <x-form.input name="pickup_date" label="Tanggal pengangkutan" type="date"
        :value="$pickup->pickup_date?->toDateString()" :max="now()->toDateString()" required />
    <x-form.select name="partner_id" label="Mitra tujuan" :options="$partners" :value="$pickup->partner_id"
        placeholder="-- Pilih mitra --" required />
    <div class="md:col-span-2">
        <x-form.textarea name="notes" label="Catatan (misalnya kendaraan, petugas pengangkut)" :value="$pickup->notes" rows="2" />
    </div>
</div>
@if ($partners->isEmpty())
    <p class="mt-2 text-sm text-amber-700">Belum ada mitra aktif. Tambahkan dulu di menu Mitra.</p>
@endif
