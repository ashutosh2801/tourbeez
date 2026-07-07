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

        $discountableTotal = (float) $request->input('discountable_cart_total', $request->cart_total);
        $discountableTotalPayLater = (float) $request->input('discountable_cart_totalPayLater', $request->cart_totalPayLater ?? $request->cart_total);
        $taxableTotal = (float) $request->input('taxable_cart_total', $request->cart_total);
        $taxableTotalPayLater = (float) $request->input('taxable_cart_totalPayLater', $request->cart_totalPayLater ?? $request->cart_total);

        // Calculate discount only on eligible tour items. Taxes apply after discount.
        $discount = $discountPayLater = 0;

        if ($promo->value_type === 'VALUE_LIMITPRODUCT') {
            $discount = min($promo->voucher_value, $discountableTotal);
            $discountPayLater = min($promo->voucher_value, $discountableTotalPayLater);
        }
        else if ($promo->value_type === 'VALUE') {
            $discount = min($promo->voucher_value, $discountableTotal);
            $discountPayLater = min($promo->voucher_value, $discountableTotalPayLater);
        }
        else if ($promo->value_type === 'VALUE_LIMITCATEGORY') {
            $discount = min($promo->voucher_value, $discountableTotal);
            $discountPayLater = min($promo->voucher_value, $discountableTotalPayLater);
        }
        else if ($promo->value_type === 'PERCENT_LIMITPRODUCT') {
            $discount = ($discountableTotal * $promo->value_percent) / 100;
            $discountPayLater = ($discountableTotalPayLater * $promo->value_percent) / 100;
        }
        else if ($promo->value_type === 'PERCENT') {
            $discount = ($discountableTotal * $promo->value_percent) / 100;
            $discountPayLater = ($discountableTotalPayLater * $promo->value_percent) / 100;
        }
        else if ($promo->value_type === 'PERCENT_LIMITCATEGORY') {
            $discount = ($discountableTotal * $promo->value_percent) / 100;
            $discountPayLater = ($discountableTotalPayLater * $promo->value_percent) / 100;
        }

        // Pay Now
        $sub_total = max(0, ($taxableTotal - $discount));
        $hst_value = round($sub_total * 0.13, 2);
        $final_total = max(0, ($sub_total + $hst_value));

        // Pay Later
        $sub_totalPayLater = max(0, ($taxableTotalPayLater - $discountPayLater));
        $hst_valuePayLater = round($sub_totalPayLater * 0.13, 2);
        $final_totalPayLater = max(0, ($sub_totalPayLater + $hst_valuePayLater));

        return response()->json([
            'success'   => true,
            'code'      => $promo->code,
            'type'      => $promo->value_type,

            'is_pay_later'          => ($request->cart_totalPayLater === $request->cart_total) ? 0 : 1,

            'discount'              => round($discount, 2),
            'hst_value'             => $hst_value, // Assuming HST is 13%
            'sub_total'             => $sub_total,
            'final_total'           => $final_total,

            'discount_paylater'     => round($discountPayLater, 2),
            'hst_value_paylater'    => $hst_valuePayLater, // Assuming HST is 13%
            'sub_total_paylater'    => $sub_totalPayLater,
            'final_total_paylater'  => $final_totalPayLater,
        ]);
    }
}
