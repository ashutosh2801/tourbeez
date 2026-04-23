<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stripe_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('event_id')->nullable();
            $table->string('event_type')->nullable();
            $table->string('payment_intent_id')->nullable();
            //$table->longText('payload')->nullable();
            $table->json('payload')->nullable();
            $table->text('status')->nullable(); // success / failed / ignored
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_webhook_logs');
    }
};
