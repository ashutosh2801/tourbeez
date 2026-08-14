<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderActions;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Support\Str;

class OrderObserver
{
    private const SOURCE_ACTION_PREFIX = 'Order source:';

    public function created(Order $order): void
    {
        $this->saveSourceAction($order);
        $this->saveCreationAction($order);
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('source')) {
            $this->saveSourceAction($order);
        }
    }

    private function saveSourceAction(Order $order): void
    {
        $source = strtolower(trim((string) ($order->source ?: 'tourbeez')));

        if ($source === 'tourbeez') {
            $sourceLabel = 'Tourbeez (Website)';
        } elseif ($source === 'internal') {
            $sourceLabel = 'Tourbeez (Internal/Admin)';
        } else {
            $partnerName = Partner::query()
                ->whereRaw('LOWER(slug) = ?', [$source])
                ->value('name');
            $sourceLabel = ($partnerName ?: Str::headline($source)) . ' (Third Party)';
        }

        $action = OrderActions::query()
            ->where('order_id', $order->id)
            ->where('notes', 'like', self::SOURCE_ACTION_PREFIX . '%')
            ->first();

        if (!$action) {
            $action = new OrderActions();
            $action->order_id = $order->id;
            $action->performed_by = $order->created_by ?: null;
        }

        $action->notes = self::SOURCE_ACTION_PREFIX . ' ' . $sourceLabel;
        $action->save();
    }

    private function saveCreationAction(Order $order): void
    {
        if (empty($order->created_by)) {
            return;
        }

        $creator = User::query()->find($order->created_by);
        $creatorName = $creator?->name ?: $creator?->first_name ?: 'Staff';

        $note = 'Order created by ' . trim($creatorName) . '.';

        $exists = OrderActions::query()
            ->where('order_id', $order->id)
            ->where('notes', $note)
            ->exists();

        if ($exists) {
            return;
        }

        OrderActions::create([
            'order_id' => $order->id,
            'performed_by' => $order->created_by,
            'notes' => $note,
        ]);
    }
}
