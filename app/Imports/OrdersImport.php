<?php

namespace App\Imports;

use App\Models\Order;
use App\Models\OrderTour;
use App\Models\OrderCustomer;
use App\Models\Tour;
use App\Models\Addon;
use App\Mail\OrderImportFailureReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
        foreach ($rows as $row) {

            $this->currentRow++;

            if (empty($row['order_number'])) {
                continue;
            }

            $this->total++;

            $orderNumber = $row['order_number'];

            /* ---------- Duplicate ---------- */
            if (Order::where('redzy_order_id', $orderNumber)->exists()) {
                $this->skipped++;
                $this->addFailure($orderNumber, ['Duplicate order number']);
                continue;
            }

            /* ---------- Tour ---------- */
            $tour = Tour::whereRaw('LOWER(title) = ?', [strtolower(trim($row['product_name'] ?? ''))])->first();

            if (!$tour) {
                $this->addFailure($orderNumber, ['Tour not found: '.$row['product_name']]);
                continue;
            }

            try {

                DB::transaction(function () use ($row, $tour, $orderNumber) {

                    $createdAt = Carbon::parse($row['date']);
                    $tourDate  = Carbon::parse($row['check_in']);

                    /* ================= PARSE PRICING ================= */

                    $quantities     = array_map('trim', explode(',', $row['quantities'] ?? ''));
                    $quantityLabels = array_map('trim', explode(',', $row['quantities_label'] ?? ''));

                    $pricing = [];
                    $totalGuests = 0;
                    $itemTotal   = 0;

                    foreach ($quantities as $index => $qty) {

                        $qty = (int) $qty;
                        if ($qty <= 0) continue;

                        $label = $quantityLabels[$index] ?? null;

                        if (!$label) {
                            throw new \Exception("Missing pricing label at index {$index}");
                        }

                        $tourPricing = $tour->pricings()
                            ->whereRaw('LOWER(label) = ?', [strtolower($label)])
                            ->first();

                        if (!$tourPricing) {
                            throw new \Exception("Pricing not found: {$label}");
                        }

                        $total = $tourPricing->price * $qty;

                        $pricing[] = [
                            'tour_id'         => $tour->id,
                            'tour_pricing_id' => $tourPricing->id,
                            'label'           => $label,
                            'quantity'        => $qty,
                            'price'           => $tourPricing->price,
                            'total_price'     => $total,
                        ];

                        $totalGuests += $qty;
                        $itemTotal   += $total;
                    }

                    /* ================= PARSE EXTRAS ================= */

                    $extras      = array_map('trim', explode(',', $row['extras'] ?? ''));
                    $extraLabels = array_map('trim', explode(',', $row['extras_label'] ?? ''));

                    $extraData = [];

                    foreach ($extras as $index => $qty) {

                        $qty = (int) $qty;
                        if ($qty <= 0) continue;

                        $label = $extraLabels[$index] ?? null;

                        if (!$label) {
                            throw new \Exception("Missing addon label at index {$index}");
                        }

                        $addon = Addon::where('tour_id', $tour->id)
                            ->whereRaw('LOWER(name) = ?', [strtolower($label)])
                            ->first();

                        if (!$addon) {
                            throw new \Exception("Addon not found: {$label}");
                        }

                        $total = $addon->price * $qty;

                        $extraData[] = [
                            'tour_id'       => $tour->id,
                            'tour_extra_id' => $addon->id,
                            'label'         => $label,
                            'quantity'      => $qty,
                            'price'         => $addon->price,
                            'total_price'   => $total,
                        ];

                        $itemTotal += $total;
                    }

                    /* ================= CREATE ORDER ================= */

                    $order = Order::create([
                        'tour_id'          => $tour->id,
                        'user_id'          => 0,
                        'order_number'     => $orderNumber,
                        'redzy_order_id'   => $orderNumber,
                        'currency'         => 'USD',
                        'order_status'     => $this->mapOrderStatus($row['order_status']),
                        'payment_status'   => ($row['order_total_paid'] >= $row['order_total_amount']) ? 1 : 0,
                        'number_of_guests' => $totalGuests,
                        'total_amount'     => $row['order_total_amount'],
                        'booked_amount'    => $row['order_total_paid'],
                        'balance_amount'   => $row['order_balance'],
                        'created_at'       => $createdAt,
                    ]);

                    /* ================= CREATE ORDER TOUR ================= */

                    OrderTour::create([
                        'order_id'         => $order->id,
                        'tour_id'          => $tour->id,
                        'tour_date'        => $tourDate->toDateString(),
                        'tour_time'        => $row['pick_up_time'] ?? null,
                        'tour_pricing'     => json_encode($pricing),
                        'tour_extra'       => json_encode($extraData),
                        'tour_fees'        => json_encode([]),
                        'number_of_guests' => $totalGuests,
                        'total_amount'     => $row['order_total_amount'],
                    ]);

                    /* ================= CUSTOMER ================= */

                    [$first, $last] = array_pad(
                        explode(' ', trim($row['customer_full_name']), 2),
                        2,
                        ''
                    );

                    OrderCustomer::create([
                        'order_id'   => $order->id,
                        'first_name' => $first,
                        'last_name'  => $last,
                        'email'      => $row['customer_email'],
                        'phone'      => $row['customer_phone'],
                        'pickup_name'=> $row['pick_up_location'] ?? null,
                    ]);
                });

                $this->imported++;

            } catch (\Throwable $e) {

                $this->addFailure($orderNumber, [$e->getMessage()]);

                Log::error('Order import row failed', [
                    'row' => $this->currentRow,
                    'order_number' => $orderNumber,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function addFailure(string $orderNumber, array $errors): void
    {
        $this->failed++;

        $this->failReasons[] = [
            'order_number' => $orderNumber,
            'row' => $this->currentRow,
            'errors' => $errors
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function () {

                Log::channel('daily')->info('Order import completed', [
                    'total'    => $this->total,
                    'imported' => $this->imported,
                    'skipped'  => $this->skipped,
                    'failed'   => $this->failed,
                ]);

                if (!empty($this->failReasons)) {

                    Mail::to(config('mail.from.address'))
                        ->send(new OrderImportFailureReport(
                            $this->failReasons,
                            $this->total,
                            $this->imported,
                            $this->failed,
                            $this->skipped
                        ));
                }
            },
        ];
    }

    private function mapOrderStatus(?string $status): int
    {
        $status = strtolower(trim($status ?? ''));

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
