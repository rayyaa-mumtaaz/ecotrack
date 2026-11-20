<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->string('deposit_number', 30)->unique();
            // restrict: user/drop point yang punya setoran tidak bisa dihapus agar riwayat tetap utuh
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('drop_point_id')->constrained()->restrictOnDelete();
            $table->date('deposit_date');
            // total berat (kg), dihitung dari deposit_items.weight
            $table->decimal('total_weight', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['collected', 'shipped', 'completed'])->default('collected');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
