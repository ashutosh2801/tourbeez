<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

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

}
