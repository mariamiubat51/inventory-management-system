<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->string('invoice_no')->unique();
            $table->date('purchase_date');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2);
            $table->decimal('due_amount', 12, 2);
            $table->integer('item_count')->default(0); 
            $table->enum('payment_status', ['fully_paid', 'partially_paid', 'unpaid']);
            $table->enum('purchase_status', ['completed', 'pending', 'cancelled']);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
