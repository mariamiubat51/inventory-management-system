<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            // Company Info
            $table->string('company_name')->nullable();
            $table->string('logo')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // System Preferences
            $table->string('timezone')->default('Asia/Dhaka');
            $table->string('currency')->default('BDT');

            // Inventory Settings
            $table->integer('low_stock_alert')->default(10);
            $table->string('default_unit')->default('pcs');

            $table->timestamps();
        });

        // Insert default row (only one row will be used)
        DB::table('settings')->insert([
            'company_name' => 'My Company',
            'logo' => null,
            'address' => 'Dhaka, Bangladesh',
            'phone' => '01700000000',
            'email' => 'info@company.com',
            'timezone' => 'Asia/Dhaka',
            'currency' => 'BDT',
            'low_stock_alert' => 10,
            'default_unit' => 'pcs',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
