<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            UPDATE order_payments AS payment
            LEFT JOIN orders AS `order` ON `order`.id = payment.order_id
            SET payment.current_rate = COALESCE(NULLIF(`order`.current_rate, 0), 1)
            WHERE payment.current_rate IS NULL OR payment.current_rate <= 0
        SQL);

        DB::statement(
            'ALTER TABLE order_payments '
            . 'MODIFY current_rate DECIMAL(16, 8) NOT NULL DEFAULT 1'
        );
    }

    public function down(): void
    {
        DB::statement(
            'ALTER TABLE order_payments '
            . 'MODIFY current_rate FLOAT NULL DEFAULT NULL'
        );
    }
};
