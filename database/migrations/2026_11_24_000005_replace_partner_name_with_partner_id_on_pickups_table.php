<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mitra tujuan pengangkutan sekarang diambil dari tabel partners,
     * menggantikan kolom teks partner_name.
     * Nullable agar migration aman untuk data lama; wajib diisi lewat validasi form.
     */
    public function up(): void
    {
        Schema::table('pickups', function (Blueprint $table) {
            $table->foreignId('partner_id')->nullable()->after('pickup_date')
                ->constrained()->restrictOnDelete();
        });

        Schema::table('pickups', function (Blueprint $table) {
            $table->dropColumn('partner_name');
        });
    }

    public function down(): void
    {
        Schema::table('pickups', function (Blueprint $table) {
            $table->string('partner_name', 150)->default('')->after('pickup_date');
        });

        Schema::table('pickups', function (Blueprint $table) {
            $table->dropConstrainedForeignId('partner_id');
        });
    }
};
