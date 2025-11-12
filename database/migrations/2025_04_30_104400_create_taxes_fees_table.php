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
        Schema::create('taxes_fees', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('tax_fee_type');
            $table->string('fee_type');
            $table->string('tax_fee_value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxes_fees');
    }
};
