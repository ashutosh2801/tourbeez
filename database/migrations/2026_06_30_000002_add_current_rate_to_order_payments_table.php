<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('order_payments', 'current_rate')) {
            Schema::table('order_payments', function (Blueprint $table) {
                $table->decimal('current_rate', 16, 8)->default(1)->after('currency');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('order_payments', 'current_rate')) {
            Schema::table('order_payments', function (Blueprint $table) {
                $table->dropColumn('current_rate');
            });
        }
    }
};
