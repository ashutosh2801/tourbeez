<?php

namespace App\Http\Controllers;

use App\Models\OrderLog;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    // public function index(Request $request) {
    //     $logs = Activity::latest()->paginate(20); // you can also use ->get() if you don’t want pagination
    //     return view('admin.activity_logs.index', compact('logs'));
    // }

    public function index(Request $request)
    {
        $query = Activity::with('subject');

        // 🔍 Order Number filter

       $query = Activity::query();

        // Order number
        if ($request->filled('order_number')) {
            $orderNumber = $request->order_number;

            $query->where(function ($q) use ($orderNumber) {
                $q->where('properties', 'LIKE', "%{$orderNumber}%")
                  ->orWhere(function ($sub) use ($orderNumber) {
                      $sub->where('subject_type', \App\Models\Order::class)
                          ->whereIn('subject_id', function ($q2) use ($orderNumber) {
                              $q2->select('id')
                                 ->from('orders')
                                 ->where('order_number', 'LIKE', "%{$orderNumber}%");
                          });
                  });
            });
        }

        // Model
        if ($request->filled('model')) {
            $query->where('subject_type', $request->model);
        }

        // Model ID
        if ($request->filled('model_id')) {
            $query->where('subject_id', $request->model_id);
        }

        // User
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        // Action
        if ($request->filled('action')) {
            $query->where('description', $request->action);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'LIKE', "%{$request->search}%")
                  ->orWhere('log_name', 'LIKE', "%{$request->search}%")
                  ->orWhere('properties', 'LIKE', "%{$request->search}%");
            });
        }

        // Property
        if ($request->filled('property')) {
            $query->where('properties', 'LIKE', "%{$request->property}%");
        }

        // Date
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->with('subject')->latest()
            ->paginate(20)
            ->appends($request->all());

        return view('admin.activity_logs.index', compact('logs'));
    }

    public function descriptive(Request $request)
    {
        $query = Activity::with(['subject', 'causer']);

        // SAME FILTERS reuse karo
        if ($request->filled('order_number')) {
            $orderNumber = $request->order_number;

            $query->where(function ($q) use ($orderNumber) {
                $q->where('properties', 'LIKE', "%{$orderNumber}%")
                  ->orWhere(function ($sub) use ($orderNumber) {
                      $sub->where('subject_type', \App\Models\Order::class)
                          ->whereIn('subject_id', function ($q2) use ($orderNumber) {
                              $q2->select('id')
                                 ->from('orders')
                                 ->where('order_number', 'LIKE', "%{$orderNumber}%");
                          });
                  });
            });
        }

        if ($request->filled('model')) {
            $query->where('subject_type', $request->model);
        }

        if ($request->filled('action')) {
            $query->where('description', $request->action);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->latest()
            ->paginate(20)
            ->appends($request->all());

        return view('admin.activity_logs.descriptive', compact('logs'));
    }


    public function orderLog4234(Request $request)
    {
        $query = OrderLog::query();

        // ✅ Filters
        if ($request->filled('order_id')) {
            $query->where('order_id', $request->order_id);
        }

        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_intent_id')) {
            $query->where('payment_intent_id', $request->payment_intent_id);
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->filled('search')) {
            $query->where('message', 'LIKE', "%{$request->search}%");
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // ✅ Get grouped orders
        $logs = $query
            ->select(
                'order_id',
                DB::raw('MAX(id) as last_log_id'),
                DB::raw('MAX(created_at) as last_time')
            )
            ->groupBy('order_id')
            ->orderByDesc('last_time')
            ->paginate(20);

        // ✅ Get all order_ids in current page
        $orderIds = $logs->pluck('order_id');

        // ✅ Fetch ALL logs in ONE query (important)
        $allLogs = OrderLog::whereIn('order_id', $orderIds)
            ->orderBy('id')
            ->get()
            ->groupBy('order_id');

        // ✅ Attach data (NO extra queries now)
        $logs->getCollection()->transform(function ($row) use ($allLogs) {

            $orderLogs = $allLogs[$row->order_id] ?? collect();

            // Ensure collection of objects only
            $orderLogs = $orderLogs->filter(function ($log) {
                return is_object($log);
            })->values();

            return (object)[
                'order_id'  => $row->order_id,
                'steps'     => $orderLogs, // ALWAYS objects
                'lastLog'   => $orderLogs->last() ?: null,
                'lastError' => $orderLogs
                    ->whereIn('status', ['failed','error'])
                    ->last() ?: null,
            ];
        });

        return view('admin.activity_logs.order-log', compact('logs'));
    }



    public function orderLog(Request $request)
{
    $query = OrderLog::query();

    // ✅ Filters
    if ($request->filled('order_id')) {
        $query->where('order_id', $request->order_id);
    }

    if ($request->filled('stage')) {
        $query->where('stage', $request->stage);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('payment_intent_id')) {
        $query->where('payment_intent_id', $request->payment_intent_id);
    }

    if ($request->filled('event_id')) {
        $query->where('event_id', $request->event_id);
    }

    if ($request->filled('search')) {
        $query->where('message', 'LIKE', "%{$request->search}%");
    }

    if ($request->filled('start_date')) {
        $query->whereDate('created_at', '>=', $request->start_date);
    }

    if ($request->filled('end_date')) {
        $query->whereDate('created_at', '<=', $request->end_date);
    }

    // ✅ GROUP BY ORDER
    $logs = $query
        ->whereNotNull('order_id') // 🔥 IMPORTANT FIX
        ->select(
            'order_id',
            DB::raw('MAX(id) as last_log_id'),
            DB::raw('MAX(created_at) as last_time')
        )
        ->groupBy('order_id')
        ->orderByDesc('last_time')
        ->paginate(20);

    $orderIds = $logs->pluck('order_id');

    // ✅ Fetch all logs in one go
    $allLogs = OrderLog::whereIn('order_id', $orderIds)
        ->orderBy('id')
        ->get()
        ->groupBy('order_id');

    // ✅ Transform safely
    $logs->getCollection()->transform(function ($row) use ($allLogs) {

        $orderLogs = collect($allLogs[$row->order_id] ?? []);

        $orderLogs = $orderLogs->filter(function ($log) {
            return is_object($log) && isset($log->stage);
        })->values();

        return (object)[
            'order_id'  => $row->order_id,
            'steps'     => $orderLogs,
            'lastLog'   => $orderLogs->last(),
            'lastError' => $orderLogs
                ->whereIn('status', ['failed','error'])
                ->last(),
        ];
    });

    return view('admin.activity_logs.order-log', compact('logs'));
}

}
