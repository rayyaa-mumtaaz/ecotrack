<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin/petugas yang menerima barang dari penyetor.
     * Nullable agar migration aman untuk data lama; diisi otomatis saat setoran dicatat.
     */
    public function up(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->foreignId('received_by')->nullable()->after('user_id')
                ->constrained('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('received_by');
        });
    }
};
