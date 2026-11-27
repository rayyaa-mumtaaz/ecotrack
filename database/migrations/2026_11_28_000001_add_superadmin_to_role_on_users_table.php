<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan role superadmin (koordinator) ke kolom role.
     * Role menjadi: superadmin (koordinator), admin (petugas drop point), user (penyetor).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['superadmin', 'admin', 'user'])->default('user')->change();
        });
    }

    public function down(): void
    {
        // Superadmin dikembalikan menjadi admin agar tidak melanggar enum lama
        DB::table('users')->where('role', 'superadmin')->update(['role' => 'admin']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'user'])->default('user')->change();
        });
    }
};
