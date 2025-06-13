<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class DashboardController extends Controller
{
   public function index(Request $request)
    {
    
        $userId = $request->user_id;
        $user = User::with(['tours', 'orders'])->find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'tours_count' => $user->tours->count(),
            'orders_count' => $user->orders->count(),
            'states_count' => $user->states->count(),

            'tours' => $user->tours,
            'orders' => $user->orders,
            'states' => $user->states,
        ]);

    }
}