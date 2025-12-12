<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promo;
use Carbon\Carbon;

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
        return view('admin.promos.create');
    }

    /** Store promo */
    public function store(Request $request)
    {
        $data = $request->validate([
            'Promos.code' => 'required|unique:promos,code',
            'Promos.status' => 'required|in:ISSUED,EXPIRED',
            'Promos.quantityRule' => 'required|in:ORDER,PRODUCT,QUANTITY',
            'Promos.issueDate' => 'nullable|date',
            'Promos.expiryDate' => 'nullable|date|after_or_equal:Promos.issueDate',
            'Promos.travelFromDate' => 'nullable|date',
            'Promos.travelToDate' => 'nullable|date|after_or_equal:Promos.travelFromDate',
            'Promos.redemptionLimit' => 'required|in:UNLIMITED,LIMITED',
            'Promos.maxUses' => 'nullable|integer|min:1',
            'Promos.minAmount' => 'nullable|numeric|min:0',
            'Promos.includeTaxesAndFees' => 'nullable|boolean',
            'Promos.includeExtras' => 'nullable|boolean',
            'Promos.internal' => 'nullable|boolean',
            'Promos.valueType' => 'required|in:VALUE_LIMITPRODUCT,VALUE,VALUE_LIMITCATALOG,PERCENT_LIMITPRODUCT,PERCENT,PERCENT_LIMITCATALOG',
            'Promos.voucherValue' => 'nullable|numeric|min:0',
            'Promos.valuePercent' => 'nullable|numeric|min:0|max:100',
            'Promos.internalNotes' => 'nullable|string',
            'validRedemptionDaysIndex' => 'nullable|array',
            'validRedemptionDaysIndex.*' => 'integer|between:1,7',
            'Product.id' => 'nullable|exists:products,id',
            'Category.id' => 'nullable|exists:catalogs,id',
        ]);

        // Flatten the nested array for model
        $promoData = $data['Promos'];
        $promoData['used_count'] = 0;

        // Store validRedemptionDaysIndex separately if needed
        $promoData['valid_days'] = $data['validRedemptionDaysIndex'] ?? [];

        // Assign product or catalog if selected
        $promoData['product_id'] = $data['Product']['id'] ?? null;
        $promoData['category_id'] = $data['Category']['id'] ?? null;

        Promo::create($promoData);

        return redirect()->route('admin.promos.index')
                         ->with('success', 'Promo created successfully.');
    }

    /** Show edit form */
    public function edit($id)
    {
        $promo = Promo::findOrFail($id);
        return view('admin.promos.edit', compact('promo'));
    }

    /** Update promo */
    public function update(Request $request, $id)
    {
        $promo = Promo::findOrFail($id);

        $data = $request->validate([
            'type' => 'required|in:PERCENT,FIXED',
            'value' => 'required|numeric|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE,EXPIRED',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'max_uses' => 'nullable|integer|min:1',
        ]);

        // Prevent manual override of used_count
        unset($data['used_count']);

        $promo->update($data);

        return redirect()->route('admin.promos.index')
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
}
