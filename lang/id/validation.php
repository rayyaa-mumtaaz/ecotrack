<?php

/*
|--------------------------------------------------------------------------
| Pesan Validasi Bahasa Indonesia (EcoTrack)
|--------------------------------------------------------------------------
| Dipakai otomatis karena APP_LOCALE=id. Hanya aturan yang dipakai proyek
| yang diterjemahkan; aturan lain memakai pesan bawaan Laravel (bahasa Inggris).
| :attribute diganti dengan nama field dari bagian 'attributes' di bawah.
*/

return [
    'after_or_equal' => ':attribute harus tanggal setelah atau sama dengan :date.',
    'array' => ':attribute harus berupa daftar.',
    'before_or_equal' => ':attribute tidak boleh melebihi :date.',
    'between' => [
        'numeric' => ':attribute harus di antara :min dan :max.',
    ],
    'boolean' => ':attribute harus bernilai ya atau tidak.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'date' => ':attribute bukan tanggal yang valid.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'exists' => ':attribute yang dipilih tidak valid.',
    'gt' => [
        'numeric' => ':attribute harus lebih besar dari :value.',
    ],
    'in' => ':attribute yang dipilih tidak valid.',
    'integer' => ':attribute harus berupa bilangan bulat.',
    'max' => [
        'array' => ':attribute maksimal :max item.',
        'numeric' => ':attribute tidak boleh lebih dari :max.',
        'string' => ':attribute maksimal :max karakter.',
    ],
    'min' => [
        'array' => ':attribute minimal :min item.',
        'numeric' => ':attribute minimal :min.',
        'string' => ':attribute minimal :min karakter.',
    ],
    'numeric' => ':attribute harus berupa angka.',
    'required' => ':attribute wajib diisi.',
    'required_with' => ':attribute wajib diisi jika :values diisi.',
    'string' => ':attribute harus berupa teks.',
    'unique' => ':attribute sudah digunakan.',

    'attributes' => [
        'name' => 'nama',
        'email' => 'email',
        'password' => 'password',
        'role' => 'role',
        'drop_point_id' => 'drop point',
        'address' => 'alamat',
        'description' => 'deskripsi',
        'latitude' => 'latitude',
        'longitude' => 'longitude',
        'type' => 'tipe mitra',
        'contact_person' => 'nama kontak',
        'phone' => 'telepon',
        'user_id' => 'penyetor',
        'deposit_date' => 'tanggal setoran',
        'notes' => 'catatan',
        'status' => 'status',
        'items' => 'detail barang',
        'items.*.item_name' => 'nama barang',
        'items.*.category_id' => 'kategori',
        'items.*.quantity' => 'jumlah',
        'items.*.weight' => 'berat',
        'items.*.condition' => 'kondisi',
        'items.*.notes' => 'catatan barang',
    ],
];
