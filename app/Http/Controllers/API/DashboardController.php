<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tour;

class DashboardController extends Controller
{

    public function index(Request $request)
    {
        $userId = $request->user_id;
        $user = User::with(['tours', 'orders', 'itineraries.tour'])->find($userId);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $visitedTours = $user->itineraries
            ->whereNotNull('tour_id')
            ->pluck('tour')
            ->filter()      
            ->unique('id')
            ->values();  

            return response()->json([
                'user_id'              => $user->id,
                'tours_count'          => $user->tours->count(),
                'visited_tours_count'  => $visitedTours->count(),
                'orders_count'         => $user->orders->count(),

                'tours' => $user->tours->map(function ($tour) {
                    return [
                        'id'       => $tour->id,
                        'title'    => $tour->title,
                        'location' => $tour->location ?? null,
                    ];
            }),

            'visited_tours' => $visitedTours->map(function ($tour) {
                return [
                    'id'       => $tour->id,
                    'title'    => $tour->title,
                    'location' => $tour->location ?? null,
                ];
            }),

            'orders' => $user->orders->map(function ($order) {
                return [
                    'id'         => $order->id,
                    'order_no'   => $order->order_number,
                    'amount'     => $order->amount,
                    'status'     => $order->status,
                    'created_at' => $order->created_at->toDateString(),
                ];
            }),
        ]);
    }

    public function tourlist(Request $request)
    {
        $query = Tour::with(['detail', 'location','pricings']);
        $query->where(function ($q) use ($request) {

            if ($request->filled('tour')) {
                $q->orWhere('tour', 'like', '%' . $request->title . '%');
                $q->orWhereDate('created_at', $request->start_date);
            }

            if ($request->filled('location')) {
                $q->orWhereHas('location', function ($loc) use ($request) {
                    $loc->where('destination', 'like', '%' . $request->location . '%')
                        ->orWhere('address', 'like', '%' . $request->location . '%');
                });
            }
            if ($request->filled('description')) {
                $q->orWhereHas('detail', function ($detail) use ($request) {
                    $detail->where('description', 'like', '%' . $request->description . '%')
                           ->orWhere('long_description', 'like', '%' . $request->description . '%');
                });
            }
            if ($request->filled('min_price') && $request->filled('max_price')) {
                $q->whereHas('pricings', function ($price) use ($request) {
                    $price->whereBetween('price', [$request->min_price, $request->max_price]);
                });
            }
        });

         $tours = $query->get();

            // Filter out null fields from each tour object
            $fianalTours = $tours->map(function ($tour) {
                return collect($tour)->map(function ($value) {
                    return is_array($value) ? array_filter($value, fn($v) => !is_null($v)) : $value;
                })->filter(fn($val) => !is_null($val));
            });

            if ($fianalTours->isNotEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tours fetched successfully',
                    'data' => $fianalTours
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No tour matched your criteria',
                    'data' => []
                ]);
            }
        }
    

    public function singletour(Request $request)
    {
        $query = Tour::with(['detail', 'location','pricings']);
        $query->where(function ($q) use ($request) {

            if ($request->filled('tour')) {
                $q->orWhere('tour', 'like', '%' . $request->title . '%');
                $q->orWhereDate('created_at', $request->start_date);
            }

            if ($request->filled('location')) {
                $q->orWhereHas('location', function ($loc) use ($request) {
                    $loc->where('destination', 'like', '%' . $request->location . '%')
                        ->orWhere('address', 'like', '%' . $request->location . '%');
                });
            }
            if ($request->filled('description')) {
                $q->orWhereHas('detail', function ($detail) use ($request) {
                    $detail->where('description', 'like', '%' . $request->description . '%')
                           ->orWhere('long_description', 'like', '%' . $request->description . '%');
                });
            }
            if ($request->filled('min_price') && $request->filled('max_price')) {
                    $q->whereHas('pricings', function ($price) use ($request) {
                        $min = (float) $request->min_price;
                        $max = (float) $request->max_price;

                        $price->whereBetween('price', [$min, $max]);
                    });
                }
        });

        $tour = $query->first();
        if ($tour) {
            // Remove null fields from the result
            $cleanedTour = collect($tour)->filter(fn($value) => !is_null($value));

            return response()->json([
                'success' => true,
                'message' => 'Tour found successfully',
                'data'    => $cleanedTour
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No tour matched your criteria',
                'data'    => null
            ]);
        }
    }
}