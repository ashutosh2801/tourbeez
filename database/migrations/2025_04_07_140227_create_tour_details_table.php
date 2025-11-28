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
        Schema::create('tour_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained('tours')->onDelete('cascade');
            $table->string('description')->nullable();
            $table->text('long_description')->nullable();
            $table->tinyInteger('purchased_a_gift')->default(0);
            $table->tinyInteger('expiry_days')->default(0);
            $table->tinyInteger('expiry_date')->default(0);
            $table->tinyInteger('gift_tax_fees')->default(0);
            $table->tinyInteger('is_t_and_c')->default(0);
            $table->text('terms_and_conditions')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_details');
    }
};
