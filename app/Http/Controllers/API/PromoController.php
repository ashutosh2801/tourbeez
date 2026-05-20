<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Promo;
use Carbon\Carbon;

class PromoController extends Controller
{
    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'cart_total' => 'required|numeric|min:0'
        ]);

        $promo = Promo::where('code', $request->code)->first();

        if (!$promo) {
            return response()->json(['message' => 'Invalid promo code'], 404);
        }

        if ($promo->status !== 'ISSUED') {
            return response()->json(['message' => 'Promo inactive'], 400);
        }

        if ($promo->expires_at && Carbon::now()->gt($promo->expires_at)) {
            return response()->json(['message' => 'Promo expired'], 400);
        }

        if ($promo->usage_limit && $promo->used_count >= $promo->usage_limit) {
            return response()->json(['message' => 'Usage limit exceeded'], 400);
        }

        if ($promo->min_cart_value && $request->cart_total < $promo->min_cart_value) {
            return response()->json([
                'message' => 'Minimum cart value not met'
            ], 400);
        }

        // Calculate discount
        $discount = 0; 

        if ($promo->value_type === 'VALUE_LIMITPRODUCT') {
            $discount = $promo->voucher_value;
        }
        else if ($promo->value_type === 'VALUE') {
            $discount = $promo->voucher_value;
        }
        else if ($promo->value_type === 'VALUE_LIMITCATEGORY') {
            $discount = $promo->voucher_value;
        }
        else if ($promo->value_type === 'PERCENT_LIMITPRODUCT') {
            $discount = ($request->cart_total * $promo->value_percent) / 100;
        }
        else if ($promo->value_type === 'PERCENT') {
            $discount = ($request->cart_total * $promo->value_percent) / 100;
        }
        else if ($promo->value_type === 'PERCENT_LIMITCATEGORY') {
            $discount = ($request->cart_total * $promo->value_percent) / 100;
        }

        $hst_value = ($request->cart_total - $discount) * 0.13; // Assuming HST is 13%
        $sub_total = max(0, $request->cart_total - $discount);
        $final_total = max(0, $sub_total + $hst_value);

        return response()->json([
            'success' => true,
            'discount' => round($discount, 2),
            'hst_value' => round($hst_value, 2), // Assuming HST is 13%
            'sub_total' => round($sub_total, 2),
            'final_total' => round($final_total, 2),
            'code' => $promo->code,
            'type' => $promo->value_type,
        ]);
    }
}
