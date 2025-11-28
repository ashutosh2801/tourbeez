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
        Schema::create('tour_special_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained('tours')->onDelete('cascade');
            $table->boolean('use_deposit')->nullable();
            $table->enum('charge', ['FULL','DEPOSIT_PERCENT','DEPOSIT_FIXED','DEPOSIT_FIXED_PER_ORDER','NONE'])->nullable();
            $table->decimal('deposit_amount', 8, 2)->nullable();
            $table->boolean('allow_full_payment')->nullable();
            $table->boolean('use_minimum_notice')->nullable();
            $table->integer('notice_days')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_special_deposits');
    }
};
