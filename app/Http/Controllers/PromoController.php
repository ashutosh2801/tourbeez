<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Promo;
use App\Models\Tour;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    /** Display all promos */
    public function index()
    {
        $promos = Promo::orderBy('id', 'desc')->get();
        return view('admin.promos.index', compact('promos'));
    }

    /** Show create form */
    public function create()
    {
        $tours = Tour::all();
        $categories = Category::all();
        return view('admin.promos.create', compact('tours', 'categories'));
    }

    /** Store promo */
    public function store(Request $request)
    {
        
        $data = $request->validate([
            'Promos.code' => 'required|unique:promos,code',
            'Promos.quantityRule' => 'required|in:ORDER,PRODUCT,QUANTITY',
            'Promos.issueDate' => 'nullable|date',
            'Promos.expiryDate' => 'nullable|date|after_or_equal:Promos.issueDate',
            'Promos.travelFromDate' => 'nullable|date',
            'Promos.travelToDate' => 'nullable|date|after_or_equal:Promos.travelFromDate',
            'Promos.redemptionLimit' => 'required|in:UNLIMITED,LIMITED',
            'Promos.maxUses' => 'nullable|integer|min:1',
            'Promos.minAmount' => 'nullable|numeric|min:0',

            'Promos.includeTaxesAndFees' => 'boolean',
            'Promos.includeExtras' => 'boolean',
            'Promos.internal' => 'boolean',

            'Promos.valueType' => 'required|in:VALUE_LIMITPRODUCT,VALUE,VALUE_LIMITCATEGORY,PERCENT_LIMITPRODUCT,PERCENT,PERCENT_LIMITCATEGORY',

            'Promos.voucherValue' => 'nullable|numeric|min:0',
            'Promos.valuePercent' => 'nullable|numeric|min:0|max:100',
            'Promos.internalNotes' => 'nullable|string',

            'validRedemptionDaysIndex' => 'nullable|array',
            'validRedemptionDaysIndex.*' => 'integer|between:1,7',

            'Product.id' => 'nullable|exists:tours,id',
            'Category.id' => 'nullable|exists:categories,id',
        ]);

        // MAIN ARRAY (Promos)

        $promoData = array_combine(
            array_map('Illuminate\Support\Str::snake', array_keys($data['Promos'])),
            array_values($data['Promos'])
        );

        // Initialize
        $promoData['used_count'] = 0;

        // Save JSON Days
        $promoData['valid_days'] = $data['validRedemptionDaysIndex'] ?? [];

        // Foreign keys
        $promoData['product_id'] = $data['Product']['id'] ?? null;
        $promoData['category_id'] = $data['Category']['id'] ?? null;

        // dd($promoData);

        Promo::create($promoData);

        return redirect()
            ->route('admin.promos.index')
            ->with('success', 'Promo created successfully.');
    }


    /** Show edit form */
    public function edit($id)
    {
        $promo = Promo::findOrFail($id);
        $tours = Tour::all();
        $categories = Category::all();

        return view('admin.promos.edit', compact('promo', 'tours', 'categories'));
    }

    /** Update promo */
    public function update(Request $request, Promo $promo)
    {
        $data = $request->validate([
            'Promos.code' => 'required|unique:promos,code,' . $promo->id,
            'Promos.quantityRule' => 'required|in:ORDER,PRODUCT,QUANTITY',
            'Promos.issueDate' => 'nullable|date',
            'Promos.expiryDate' => 'nullable|date|after_or_equal:Promos.issueDate',
            'Promos.travelFromDate' => 'nullable|date',
            'Promos.travelToDate' => 'nullable|date|after_or_equal:Promos.travelFromDate',
            'Promos.redemptionLimit' => 'required|in:UNLIMITED,LIMITED',
            'Promos.maxUses' => 'nullable|integer|min:1',
            'Promos.minAmount' => 'nullable|numeric|min:0',

            'Promos.includeTaxesAndFees' => 'boolean',
            'Promos.includeExtras' => 'boolean',
            'Promos.internal' => 'boolean',

            'Promos.valueType' => 'required|in:VALUE_LIMITPRODUCT,VALUE,VALUE_LIMITCATEGORY,PERCENT_LIMITPRODUCT,PERCENT,PERCENT_LIMITCATEGORY',

            'Promos.voucherValue' => 'nullable|numeric|min:0',
            'Promos.valuePercent' => 'nullable|numeric|min:0|max:100',
            'Promos.internalNotes' => 'nullable|string',

            'validRedemptionDaysIndex' => 'nullable|array',
            'validRedemptionDaysIndex.*' => 'integer|between:1,7',

            'Product.id' => 'nullable|exists:tours,id',
            'Category.id' => 'nullable|exists:categories,id',
        ]);

        // Convert Promos keys to snake_case
        $promoData = array_combine(
            array_map('Illuminate\Support\Str::snake', array_keys($data['Promos'])),
            array_values($data['Promos'])
        );

        // Save JSON
        $promoData['valid_days'] = $data['validRedemptionDaysIndex'] ?? [];

        // Foreign keys
        $promoData['product_id'] = $data['Product']['id'] ?? null;
        $promoData['category_id'] = $data['Category']['id'] ?? null;

        // Update record
        $promo->update($promoData);

        return redirect()
            ->route('admin.promos.index')
            ->with('success', 'Promo updated successfully.');
    }


    /** Delete promo */
    public function destroy($id)
    {
        Promo::findOrFail($id)->delete();
        return redirect()->route('admin.promos.index')
                         ->with('success', 'Promo deleted successfully.');
    }

    /**
     * Apply Promo (AJAX)
     */
    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'order_amount' => 'required|numeric|min:0',
        ]);

        $promo = Promo::where('code', $request->code)->first();

        if (! $promo) {
            return response()->json(['error' => 'Invalid promo code'], 404);
        }

        if ($promo->status !== 'ACTIVE') {
            return response()->json(['error' => 'Promo is not active'], 400);
        }

        // Date validity check
        $today = now()->startOfDay();

        if ($promo->start_date && $today->lt(Carbon::parse($promo->start_date))) {
            return response()->json(['error' => 'Promo is not active yet'], 400);
        }

        if ($promo->end_date && $today->gt(Carbon::parse($promo->end_date))) {
            return response()->json(['error' => 'Promo has expired'], 400);
        }

        // Usage limit check
        if ($promo->max_uses && $promo->used_count >= $promo->max_uses) {
            return response()->json(['error' => 'Promo usage limit reached'], 400);
        }

        // Minimum order value
        if ($promo->min_amount && $request->order_amount < $promo->min_amount) {
            return response()->json(['error' => 'Minimum order amount not met'], 400);
        }

        // Calculate discount
        if ($promo->type === 'PERCENT') {
            $discount = $request->order_amount * ($promo->value / 100);
        } else {
            $discount = $promo->value;
        }

        // Final payable
        $final_amount = max($request->order_amount - $discount, 0);

        // Auto increment usage
        $promo->increment('used_count');

        return response()->json([
            'success' => true,
            'discount' => round($discount, 2),
            'final_amount' => round($final_amount, 2),
        ]);
    }

    public function fetch_coupon($coupon)
    {
        $promo = Promo::where('code', $coupon)->first();

        if (!$promo) {
            return response()->json([
                'status' => false,
                'message' => 'Promo code not found.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'promo' => [
                'id' => $promo->id,
                'code' => $promo->code,
                'status' => $promo->status,
                'quantity_rule' => $promo->quantity_rule,
                'issue_date' => $promo->issue_date,
                'expiry_date' => $promo->expiry_date,
                'travel_from_date' => $promo->travel_from_date,
                'travel_to_date' => $promo->travel_to_date,
                'redemption_limit' => $promo->redemption_limit,
                'max_uses' => $promo->max_uses,
                'min_amount' => $promo->min_amount,
                'include_taxes_and_fees' => $promo->include_taxes_and_fees,
                'include_extras' => $promo->include_extras,
                'internal' => $promo->internal,
                'value_type' => $promo->value_type,
                'voucher_value' => $promo->voucher_value,
                'value_percent' => $promo->value_percent,
                'internal_notes' => $promo->internal_notes,
                'valid_days' => $promo->valid_days,
                'product_id' => $promo->product_id,
                'category_id' => $promo->category_id,
                'used_count' => $promo->used_count,
            ]
        ]);
    }

}
