<?php

namespace App\Imports;

use App\Models\Order;
use App\Models\OrderTour;
use App\Models\OrderCustomer;
use App\Models\Tour;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\{
    ToCollection,
    WithHeadingRow,
    WithEvents,
    WithValidation
};
use Maatwebsite\Excel\Events\AfterImport;
use Illuminate\Support\Collection;

class OrderImport23 implements ToCollection, WithHeadingRow, WithEvents
{
    public int $total = 0;
    public int $imported = 0;
    public int $skipped = 0;
    public int $failed = 0;

    public array $failReasons = [];

    protected int $currentRow = 1;

    /* ---------------- IMPORT ---------------- */

    public function collection(Collection $rows)
    {
        
        foreach ($rows as $row) {
            if($row['order_number'] == ''){
                continue;
            }
            $this->total++;
            $this->currentRow++;

            try {


                /* ---------- Duplicate ---------- */
                if (Order::where('redzy_order_id', $row['order_number'])->exists()) {
                    $this->skipped++;
                    $this->failReasons[] = [
                        'row' => $this->currentRow,
                        'reason' => 'Duplicate order number'
                    ];
                    continue;
                }

                /* ---------- Tour ---------- */
                $tour = Tour::where('title', $row['product_name'])->first();
                
                if (!$tour) {
                    $this->failed++;
                    $this->failReasons[] = [
                        'row' => $this->currentRow,
                        'reason' => 'Tour not found'
                    ];
                    continue;
                }

                /* ---------- Dates ---------- */
                $createdAt = Carbon::parse($row['order_created_at']);
                $fulfilment = Carbon::parse($row['order_fulfilment_at']);

                /* ---------- Order ---------- */
                $order = Order::create([
                    'tour_id'          => $tour->id,
                    'user_id'          => auth()->id() ?? 0,

                    'order_number'     => $row['order_number'],
                    'redzy_order_id'   => $row['order_number'],

                    'source'     =>     $row['order_source'] ?? 'Redzy',
                    'agent_name'       => $row['agent_name'] ?? null,

                    'currency'         => 'USD',
                    'payment_method'   => strtolower($row['payment_gateway_type'] ?? 'import'),

                    'payment_status'   => strtolower($row['is_all_paid']) === 'yes' ? 1 : 0,
                    'order_status' => $this->mapOrderStatus($row['order_status'] ?? ''),

                    'number_of_guests' => (int) $row['num_participant'],

                    'total_amount'     => $this->amount($row['order_total_amount']),
                    'booked_amount'    => $this->amount($row['total_payment']),
                    'balance_amount'   => $this->amount($row['order_balance']),

                    'voucher_code'     => $row['voucher_code'] ?? null,
                    'internal_notes'   => $row['order_internal_notes'] ?? null,

                    'created_at'       => $createdAt,
                ]);

                /* ---------- Order Tour ---------- */
                $orderTour = OrderTour::create([
                    'order_id'         => $order->id,
                    'tour_id'          => $tour->id,
                    'tour_date'        => $fulfilment->toDateString(),
                    'tour_time'        => $fulfilment->format('H:i'),

                    'number_of_guests' => (int) $row['num_participant'],
                    'total_amount'     => $this->amount($row['order_total_amount']),

                    'tour_pricing' => json_encode([
                        [
                            'label'       => 'Imported price',
                            'quantity'    => (int) $row['num_participant'],
                            'price'       => $this->amount($row['product_price']),
                            'total_price' => $this->amount($row['order_total_amount']),
                        ]
                    ]),

                    'tour_extra' => json_encode([
                        'extra_amount' => $this->amount($row['extra_amount']),
                    ]),

                    'tour_fees' => json_encode([
                        'tax'          => $this->amount($row['tax_amount']),
                        'booking_fee' => $this->amount($row['booking_fee']),
                    ]),
                ]);

                /* ---------- Customer ---------- */
                [$first, $last] = array_pad(
                    explode(' ', trim($row['customer_first_last_name']), 2),
                    2,
                    ''
                );

                $orderCustomer = OrderCustomer::create([
                    'order_id'    => $order->id,
                    'first_name'  => $first,
                    'last_name'   => $last,
                    'email'       => 'import_'.$order->id.'@rezdy.local',
                    'instructions'=> $row['order_agent_reference'] ?? null,
                ]);
                

                $this->imported++;

            } catch (\Throwable $e) {
                $this->failed++;
                $this->failReasons[] = [
                    'row' => $this->currentRow,
                    'reason' => $e->getMessage()
                ];
                Log::error('Order import failed', [
                    'row' => $this->currentRow,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /* ---------------- VALIDATION ---------------- */

    public function rules(): array
    {
        return [
            'order_number'          => 'required',
            'product_name'          => 'required',
            'order_created_at'      => 'required|date',
            'order_fulfilment_at'   => 'required|date',
            'order_total_amount'    => 'required|numeric',
            'num_participant'       => 'required|integer|min:1',
            'customer_first_last_name' => 'required',
        ];


    }

    /* ---------------- EVENTS ---------------- */

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function () {
                Log::info('Order import completed', [
                    'total'    => $this->total,
                    'imported' => $this->imported,
                    'skipped'  => $this->skipped,
                    'failed'   => $this->failed,
                    'failures' => $this->failReasons,
                ]);
            },
        ];
    }

    /* ---------------- HELPERS ---------------- */

    private function amount($value): float
    {
        return round((float) $value, 2);
    }

    private function mapOrderStatus(?string $status): int
    {
        $status = strtolower(trim($status));

        return match (true) {
            str_contains($status, 'confirmed')          => 5,
            str_contains($status, 'cancelled')          => 6,
            str_contains($status, 'on hold')            => 2,
            str_contains($status, 'pending supplier')   => 3,
            str_contains($status, 'pending customer')   => 4,
            str_contains($status, 'abandoned cart')     => 7,
            str_contains($status, 'abandoned')          => 1,
            default                                     => 0,
        };
    }
}
