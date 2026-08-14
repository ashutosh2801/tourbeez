<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderActions;
use App\Observers\OrderObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderCreationActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function ($table) {
            $table->id();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('order_number')->nullable();
            $table->string('source')->nullable();
            $table->string('currency')->nullable();
            $table->integer('order_status')->default(0);
            $table->integer('payment_status')->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('balance_amount', 12, 2)->default(0);
            $table->decimal('booked_amount', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_actions', function ($table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Order::observe(OrderObserver::class);
    }

    public function test_staff_created_order_adds_creation_action(): void
    {
        $staff = new \App\Models\User();
        $staff->name = 'Staff User';
        $staff->save();

        $order = Order::create([
            'created_by' => $staff->id,
            'order_number' => 'ORD-1001',
            'source' => 'internal',
            'currency' => 'CAD',
        ]);

        $this->assertDatabaseHas('order_actions', [
            'order_id' => $order->id,
            'performed_by' => $staff->id,
        ]);

        $action = OrderActions::where('order_id', $order->id)->latest()->first();
        $this->assertStringContainsString('created this order', $action->notes);
        $this->assertStringContainsString('Staff User', $action->notes);
    }
}
