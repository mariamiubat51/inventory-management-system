<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionLogsTable extends Migration
{
    public function up()
    {
        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->id();

            $table->string('transaction_type'); // purchase, payment, sale, refund, etc.
            $table->unsignedBigInteger('related_id')->nullable(); // Reference to purchase, sale, etc.
            $table->unsignedBigInteger('account_id'); // Related account (e.g., cash or bank)

            $table->decimal('amount', 15, 2); // Transaction amount
            $table->enum('type', ['debit', 'credit']); // Debit = money out, Credit = money in

            $table->dateTime('transaction_date'); // When transaction happened
            $table->string('description')->nullable(); // Optional notes or details

            $table->timestamps();

            // Foreign Key Constraint
            $table->foreign('account_id')
                  ->references('id')
                  ->on('accounts')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaction_logs');
    }
}
