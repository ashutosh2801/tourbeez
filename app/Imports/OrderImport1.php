<?php

namespace App\Imports;

use App\Models\{
    Order,
    OrderTour,
    OrderCustomer,
    Tour
};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\{
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    SkipsFailures,
    WithEvents,
    WithChunkReading
};
use Maatwebsite\Excel\Events\AfterImport;

class OrderImport1 implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    WithChunkReading
{
    use SkipsFailures;

    /** 🔢 Counters */
    public int $totalRows = 0;
    public int $imported = 0;
    public int $skipped = 0;
    public int $failed = 0;

    /** 📋 Failure reasons */
    public array $failReasons = [];

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'product_name'          => 'required|string',
            'redzy_order_id'        => 'required|string',
            'customer_full_name'    => 'required|string|max:200',
            'quantities'            => 'required|integer|min:1',

            'order_total_amount'    => 'required',
            'order_total_paid'      => 'nullable',
            'order_balance'         => 'nullable',

            'check-in'              => 'required|date',
            'date'                  => 'required|date',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'product_name.required'       => 'Tour name is missing.',
            'redzy_order_id.required'     => 'Order ID is missing.',
            'customer_full_name.required' => 'Customer name is missing.',
            'quantities.min'              => 'Guest quantity must be at least 1.',
            'check-in.required'           => 'Tour date is missing.',
            'date.required'               => 'Order date is missing.',
        ];
    }

    /**
     * Chunk size (performance)
     */
    public function chunkSize(): int
    {
        return 200;
    }

    /**
     * Import logic
     */
    public function model(array $row)
    {
        $this->totalRows++;

        return DB::transaction(function () use ($row) {

            // ------------------------------
            // 1️⃣ Find tour
            // ------------------------------
            $tour = Tour::where('title', trim($row['product_name']))->first();

            if (!$tour) {
                $this->skipped++;
                $this->failReasons[] = [
                    'row'    => $this->totalRows,
                    'reason' => 'Tour not found: '.$row['product_name']
                ];
                return null;
            }

            // ------------------------------
            // 2️⃣ Duplicate check
            // ------------------------------
            if (Order::where('redzy_order_id', $row['redzy_order_id'])->exists()) {
                $this->skipped++;
                $this->failReasons[] = [
                    'row'    => $this->totalRows,
                    'reason' => 'Duplicate order ID: '.$row['redzy_order_id']
                ];
                return null;
            }

            // ------------------------------
            // 3️⃣ Create Order
            // ------------------------------
            $order = Order::create([
                'tour_id'          => $tour->id,
                'user_id'          => auth()->id() ?? 0,
                'order_number'     => $row['redzy_order_id'],
                'redzy_order_id'   => $row['redzy_order_id'],
                'currency'         => 'USD',
                'payment_method'   => 'import',
                'payment_status'   => 1,
                'order_status'     => 1,
                'number_of_guests' => 1,
                // 'number_of_guests' => (int) $row['quantities'],
                'total_amount'     => $this->amount($row['order_total_amount']),
                'booked_amount'    => $this->amount($row['order_total_paid']),
                'balance_amount'   => $this->amount($row['order_balance']),
                'created_at'       => Carbon::parse($row['date']),
            ]);

            // ------------------------------
            // 4️⃣ Order Tour
            // ------------------------------
            OrderTour::create([
                'order_id'         => $order->id,
                'tour_id'          => $tour->id,
                'tour_date'        => Carbon::parse($row['check-in'])->format('Y-m-d'),
                'tour_time'        => $row['pick-up_time'] ?? null,
                'tour_extra'       => $row['extras'] ?? null,
                'number_of_guests' => (int) $row['quantities'],
                'total_amount'     => $this->amount($row['order_total_amount']),
                'tour_pricing'     => json_encode([
                    [
                        'label'       => 'Imported pricing',
                        'quantity'    => (int) $row['quantities'],
                        'price'       => $this->amount($row['order_total_amount']),
                        'total_price' => $this->amount($row['order_total_amount']),
                    ]
                ]),
            ]);

            // ------------------------------
            // 5️⃣ Customer
            // ------------------------------
            [$first, $last] = array_pad(
                explode(' ', trim($row['customer_full_name']), 2),
                2,
                ''
            );

            OrderCustomer::create([
                'order_id'    => $order->id,
                'first_name'  => $first,
                'last_name'   => $last,
                'email'       => 'order_'.$order->id.'@import.local',
                'phone'       => $row['customer_phone'] ?? null,
                'pickup_name' => $row['pick-up_location'] ?? null,
            ]);

            $this->imported++;

            return $order;
        });
    }

    /**
     * Validation failures
     */
    public function onFailure(...$failures)
    {
        foreach ($failures as $failure) {
            $this->failed++;
            $this->failReasons[] = [
                'row'    => $failure->row(),
                'reason' => implode(', ', $failure->errors()),
            ];
        }
    }

    /**
     * After import summary
     */
    public static function afterImport(AfterImport $event)
    {
        // nothing here, stats read from instance
    }

    /**
     * Money normalization
     */
    private function amount($value): float
    {
        return (float) str_replace(['$', ',', ' '], '', $value ?? 0);
    }
}
