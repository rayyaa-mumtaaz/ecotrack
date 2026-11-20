<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposit_items', function (Blueprint $table) {
            $table->id();
            // cascade: detail barang ikut terhapus jika transaksinya dihapus
            $table->foreignId('deposit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('item_name', 100);
            $table->unsignedInteger('quantity');
            // berat total baris ini dalam kilogram
            $table->decimal('weight', 8, 2);
            $table->enum('condition', ['working', 'damaged', 'broken']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposit_items');
    }
};
