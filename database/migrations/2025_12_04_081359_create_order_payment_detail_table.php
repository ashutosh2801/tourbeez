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
        Schema::create('order_payment_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');

            $table->string('payment_type')->nullable();         // Payment type (cash, CC, refund etc.)
            $table->string('transaction_id')->nullable();   // Reference number
            $table->string('date')->nullable();         // Payment date
            $table->decimal('amount', 10, 2)->nullable(); // Payment amount
            $table->string('collection_type')->nullable(); // Inside/outside payment gateway
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_payment_detail');
    }
};
