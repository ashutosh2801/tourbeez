<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tour_id');
                $table->unsignedBigInteger('sub_tour_id')->nullable();
                $table->unsignedBigInteger('user_id')->default(0);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->string('session_id', 100)->nullable();
                $table->string('order_number', 25);
                $table->string('redzy_order_id', 25)->default('');
                $table->integer('number_of_guests')->default(0);
                $table->integer('payment_status')->default(0);
                $table->string('payment_intent_id', 100)->nullable();
                $table->string('payment_method', 25)->nullable();
                $table->string('payment_method_id', 100)->nullable();
                $table->string('payment_intent_client_secret')->nullable();
                $table->string('card_info')->nullable();
                $table->string('transaction_id')->nullable();
                $table->double('booking_fee')->nullable();
                $table->double('total_amount')->default(0);
                $table->double('balance_amount')->nullable();
                $table->double('booked_amount')->nullable();
                $table->string('currency', 10)->default('CAD');
                $table->integer('order_status')->default(1);
                $table->string('booking_status')->default('start');
                $table->text('additional_info')->nullable();
                $table->text('internal_notes')->nullable();
                $table->boolean('admin_email_sent')->nullable();
                $table->boolean('send_feedback_email')->default(true);
                $table->boolean('is_abandon_mail_sent')->default(false);
                $table->boolean('is_abandon_mail_24_sent')->default(false);
                $table->string('stripe_customer_id', 100)->nullable();
                $table->string('action_name', 100)->nullable();
                $table->string('adv_deposite', 100)->nullable();
                $table->boolean('is_discount')->nullable();
                $table->string('source', 100)->default('tourbeez');
                $table->string('failure_message')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('order_tours')) {
            Schema::create('order_tours', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tour_id');
                $table->unsignedBigInteger('order_id');
                $table->string('tour_date', 25);
                $table->string('tour_time', 25)->nullable();
                $table->text('tour_pricing');
                $table->text('tour_extra')->nullable();
                $table->text('tour_fees')->nullable();
                $table->text('discount')->nullable();
                $table->integer('number_of_guests')->nullable();
                $table->double('total_amount')->default(0);
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('order_payments')) {
            Schema::create('order_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->string('payment_intent_id')->nullable();
                $table->string('transaction_id')->nullable();
                $table->string('refund_id')->nullable();
                $table->string('payment_method', 50)->nullable();
                $table->string('payment_type', 50)->nullable();
                $table->string('card_last4', 10)->nullable();
                $table->string('card_brand', 50)->nullable();
                $table->string('card_exp_month', 10)->nullable();
                $table->string('card_exp_year', 10)->nullable();
                $table->double('amount')->default(0);
                $table->double('refund_amount')->default(0);
                $table->string('currency', 10)->default('CAD');
                $table->string('status', 30)->nullable();
                $table->string('collection_type', 10)->default('Inside');
                $table->timestamp('collection_date')->nullable();
                $table->string('action', 50)->nullable();
                $table->string('reason')->nullable();
                $table->string('refund_reason')->nullable();
                $table->timestamp('refunded_at')->nullable();
                $table->longText('response_payload')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('order_meta')) {
            Schema::create('order_meta', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->string('name');
                $table->string('value')->nullable();
            });
        }

        if (!Schema::hasTable('order_logs')) {
            Schema::create('order_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->string('stage', 100)->nullable();
                $table->string('step', 100)->nullable();
                $table->string('status', 50)->nullable();
                $table->string('payment_status', 50)->nullable();
                $table->text('message')->nullable();
                $table->json('context')->nullable();
                $table->string('event_id')->nullable();
                $table->string('payment_intent_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_logs');
        Schema::dropIfExists('order_meta');
        Schema::dropIfExists('order_payments');
        Schema::dropIfExists('order_tours');
        Schema::dropIfExists('orders');
    }
};
