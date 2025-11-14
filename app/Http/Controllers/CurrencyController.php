<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function convert(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'from'   => 'required|string|size:3'
        ]);

        $amount = (float) $request->amount;
        $from   = strtoupper($request->from);

        // use your helper function
        $converted = currencyConvert($amount, $from, 'USD');

        return response()->json([
            'usd_amount' => $converted
        ]);
    }//
}
