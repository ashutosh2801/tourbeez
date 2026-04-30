<?php

namespace App\Exports;

use App\Models\Tour;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class ToursExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Build the filtered query and return data
     */
    public function collection()
    {
        $request = $this->request;
        $query = Tour::query();

        // 🔍 Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('unique_code', 'like', "%{$search}%");
            });
        }

        // 🏷️ Category filter
        if ($request->has('category') && $request->category != '') {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        // 📌 Status filter
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // ✍️ Author filter
        if ($request->has('author') && $request->author != '') {
            $query->where('user_id', $request->author);
        }

        // 🏙️ City filter
        if ($request->has('city') && $request->city != '') {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('city_id', $request->city);
            });
        }

        // 💰 Special deposit filter
        if ($request->has('special_deposit') && $request->special_deposit != '') {
            if ($request->special_deposit === 'active') {
                $query->whereHas('specialDeposit', function ($q) {
                    $q->where('use_deposit', 1);
                });
            } elseif ($request->special_deposit === 'not_active') {
                $query->where(function ($q) {
                    $q->whereDoesntHave('specialDeposit')
                      ->orWhereHas('specialDeposit', function ($sub) {
                          $sub->where('use_deposit', 0);
                      });
                });
            }
        }

        // 📅 Schedule filter
        if ($request->has('schedule') && $request->schedule != '') {
            if ($request->schedule === 'active') {
                $query->whereHas('schedules');
            } elseif ($request->schedule === 'not_active') {
                $query->whereDoesntHave('schedules');
            }
        }

        // ⏰ Schedule expiry filter
        if ($request->filled('schedule_expiry')) {
            $today = now()->startOfDay();

            $query->whereHas('schedules', function ($q) use ($request, $today) {
                switch ($request->schedule_expiry) {
                    case 'today':
                        $q->whereDate('until_date', $today->toDateString());
                        break;
                    case 'last_7':
                        $q->whereBetween('until_date', [
                            $today->clone()->subDays(7)->toDateString(),
                            $today->toDateString(),
                        ]);
                        break;
                    case 'last_15':
                        $q->whereBetween('until_date', [
                            $today->clone()->subDays(15)->toDateString(),
                            $today->toDateString(),
                        ]);
                        break;
                    case 'this_week':
                        $q->whereBetween('until_date', [
                            $today->clone()->startOfWeek()->toDateString(),
                            $today->clone()->endOfWeek()->toDateString(),
                        ]);
                        break;
                    case 'upcoming_15':
                        $q->whereBetween('until_date', [
                            $today->toDateString(),
                            $today->clone()->addDays(15)->toDateString(),
                        ]);
                        break;
                    case 'expired':
                        $q->where('until_date', '<', $today->toDateString());
                        break;
                }
            });
        }

        // Sort order same as index
        $query->orderByRaw('sort_order = 0')->orderBy('sort_order', 'ASC');

        return $query->with(['categories', 'location'])->get(['id', 'title', 'slug', 'unique_code', 'price', 'currency']);
    }

    /**
     * Map export columns
     */
    public function map($tour): array
    {
        // $cityName    = optional(optional($tour->location)->city)->name;
        // $stateName   = optional(optional($tour->location)->city->state ?? null)->name;
        // $countryName = optional(optional($tour->location)->city->state->country ?? null)->name;

        // // Format same as citySearch()
        // $locationText = trim(collect([$cityName, $stateName, $countryName])
        //     ->filter()
        //     ->map(fn($v) => ucwords($v))
        //     ->implode(', '));

        // if ($locationText === '') {
        //     $locationText = '-';
        // }

        $image = uploaded_asset($tour->main_image->id ?? 0, 'medium');

        return [
            $tour->unique_code,
            $tour->title,
            'https://tourbeez.com/tour/' . $tour->slug,
            $image,
            $tour->price,
            $tour->currency,
        ];
    }

    public function headings(): array
    {
        return [
            // 'ID',
            // 'Title',
            // 'URL',
            // 'SKU',
            // 'Category',
            // 'Location',
            'Product SKU',
            'Product Name',
            'Product URL',
            'Product image URL',
            'Price',
            'Currency',
        ];
    }
    public function columnWidths(): array
    {
        return [
            'A' => 20,  // SKU
            'B' => 40,  // Title
            'C' => 50,  // URL
            'D' => 50,  // Image URL
            'E' => 20,  // Price
            'F' => 20,  // Currency
        ];
    }
}
