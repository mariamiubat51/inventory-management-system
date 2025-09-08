<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();   // Piece, Kg
            $table->string('symbol', 16)->nullable(); // pc, kg
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('product_units');
    }
};
