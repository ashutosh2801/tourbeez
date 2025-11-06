<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request) {
        $logs = Activity::latest()->paginate(20); // you can also use ->get() if you don’t want pagination
        return view('admin.activity_logs.index', compact('logs'));
    }
}
