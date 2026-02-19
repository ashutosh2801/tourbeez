<?php

namespace App\Imports;

use App\Models\Order;
use App\Models\OrderTour;
use App\Models\OrderCustomer;
use App\Models\Tour;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\{
    ToCollection,
    WithHeadingRow,
    WithEvents
};
use Maatwebsite\Excel\Events\AfterImport;
use Illuminate\Support\Collection;

class OrdersImport implements ToCollection, WithHeadingRow, WithEvents
{
    public int $total = 0;
    public int $imported = 0;
    public int $skipped = 0;
    public int $failed = 0;

    public array $failReasons = [];

    protected int $currentRow = 1;
    public string $sheetName = '';

    public function collection(Collection $rows)
    {
        // dd($rows);
        foreach ($rows as $row) {

            $this->currentRow++;

            if (empty($row['order_number'])) {
                continue;
            }

            $this->total++;

            /* ---------- Validation ---------- */
            // $validator = Validator::make($row->toArray(), [
            //     'order_number'              => 'required',
            //     'product_name'              => 'required',
            //     'order_created_at'          => 'required|date',
            //     'order_fulfilment_at'       => 'required|date',
            //     'order_total_amount'        => 'required|numeric',
            //     'num_participant'           => 'required|integer|min:1',
            //     'customer_first_last_name'  => 'required',
            // ]);

            // if ($validator->fails()) {
            //     $this->addFailure($validator->errors()->all());
            //     continue;
            // }

            /* ---------- Duplicate ---------- */
            if (Order::where('redzy_order_id', $row['order_number'])->exists()) {
                $this->skipped++;
                $this->addFailure(['Duplicate order number']);
                continue;
            }

            /* ---------- Tour ---------- */
            $tour = Tour::where('title', $row['product_name'])->first();
            if (!$tour) {
                $this->addFailure(['Tour not found: '.$row['product_name']]);
                continue;
            }
            dd($row);
            try {

                DB::transaction(function () use ($row, $tour) {

                    $createdAt  = Carbon::parse($row['order_created_at']);
                    $fulfilment = Carbon::parse($row['order_fulfilment_at']);

                    $isPaid = strtolower(trim($row['is_all_paid'] ?? ''));

                    $paymentStatus = in_array($isPaid, ['yes','100%','paid','1'])
                        ? 1 : 0;

                    $order = Order::create([
                        'tour_id'          => $tour->id,
                        'user_id'          => auth()->id() ?? 0,

                        'order_number'     => $row['order_number'],
                        'redzy_order_id'   => $row['order_number'],

                        'source'           => $row['order_source'] ?? 'Redzy',
                        'agent_name'       => $row['agent_name'] ?? null,

                        'currency'         => 'USD',
                        'payment_method'   => strtolower($row['payment_gateway_type'] ?? 'import'),

                        'payment_status'   => $paymentStatus,
                        'order_status'     => $this->mapOrderStatus($row['order_status'] ?? ''),

                        'number_of_guests' => (int) $row['num_participant'],

                        'total_amount'     => $this->amount($row['order_total_amount']),
                        'booked_amount'    => $this->amount($row['total_payment'] ?? 0),
                        'balance_amount'   => $this->amount($row['order_balance'] ?? 0),

                        'created_at'       => $createdAt,
                    ]);

                    OrderTour::create([
                        'order_id'         => $order->id,
                        'tour_id'          => $tour->id,
                        'tour_date'        => $fulfilment->toDateString(),
                        'tour_time'        => $fulfilment->format('H:i'),
                        'number_of_guests' => (int) $row['num_participant'],
                        'total_amount'     => $this->amount($row['order_total_amount']),
                    ]);

                    [$first, $last] = array_pad(
                        explode(' ', trim($row['customer_full_name']), 2),
                        2,
                        ''
                    );

                    OrderCustomer::create([
                        'order_id'   => $order->id,
                        'first_name' => $first,
                        'last_name'  => $last,
                        'email'      => 'import_'.$order->id.'@rezdy.local',
                        'phone'      => $row['customer_phone']
                    ]);
                });

                $this->imported++;

            } catch (\Throwable $e) {

                $this->addFailure([$e->getMessage()]);

                Log::error('Order import row failed', [
                    'row' => $this->currentRow,
                    'order_number' => $row['order_number'] ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /* ---------- Helper for failure ---------- */

    private function addFailure(array $errors): void
    {
        $this->failed++;

        $this->failReasons[] = [
            'row' => $this->currentRow,
            'errors' => $errors
        ];
    }

    /* ---------- After Import Logging ---------- */

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function () {

                Log::channel('daily')->info('Order import completed', [
                    'total'    => $this->total,
                    'imported' => $this->imported,
                    'skipped'  => $this->skipped,
                    'failed'   => $this->failed,
                    'failures' => $this->failReasons,
                ]);
            },
        ];
    }

    private function amount($value): float
    {
        return round((float) $value, 2);
    }

    private function mapOrderStatus(?string $status): int
    {
        $status = strtolower(trim($status));

        return match (true) {
            str_contains($status, 'confirmed') => 5,
            str_contains($status, 'cancelled') => 6,
            default => 0,
        };
    }
    public function setSheetName(string $name)
    {
        $this->sheetName = $name;
    }
}
