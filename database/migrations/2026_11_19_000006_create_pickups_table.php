<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickups', function (Blueprint $table) {
            $table->id();
            $table->string('pickup_number', 30)->unique();
            $table->date('pickup_date');
            $table->string('partner_name', 150);
            // jumlah total_weight setoran yang diangkut (kg)
            $table->decimal('total_weight', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickups');
    }
};
