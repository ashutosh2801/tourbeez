<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tour;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    // Show the index page
    public function index()
    {
        // Fetch latest vouchers, 10 per page
        $vouchers = Voucher::latest()->paginate(10);

        // If you need agents for the search dropdown
        $agents = []; // or whatever your agent model is

        return view('admin.vouchers.index', compact('vouchers', 'agents'));
    }

    // Show create form
    public function create()
    {
        $tours = Tour::all();
        $categories = Category::all();
        $agents = Category::all();
        return view('admin.vouchers.create', compact('tours', 'categories', 'agents'));
    }

    // Store voucher
    public function store(Request $request)
    {
        dd($request->all());
        $data = $request->validate([
            'Voucher.createMode' => 'required|in:REZDY,MANUAL',
            'Voucher.codesList' => 'required_if:Voucher.createMode,MANUAL',
            'Voucher.quantity' => 'required_if:Voucher.createMode,REZDY|integer|min:1',
            'Voucher.issueDate' => 'nullable|date',
            'Voucher.expiryDate' => 'nullable|date|after_or_equal:Voucher.issueDate',
            'Voucher.travelFromDate' => 'nullable|date',
            'Voucher.travelToDate' => 'nullable|date|after_or_equal:Voucher.travelFromDate',
            'validRedemptionDaysIndex' => 'nullable|array',
            'validRedemptionDaysIndex.*' => 'integer|between:1,7',
            'Voucher.agent.id' => 'nullable|exists:agents,id',
            'Voucher.internalReference' => 'nullable|string|max:200',
            'Voucher.minAmount' => 'nullable|numeric|min:0',
            'Voucher.includeTaxesAndFees' => 'boolean',
            'Voucher.includeExtras' => 'boolean',
            'Voucher.valueType' => 'required|in:VALUE_LIMITPRODUCT,VALUE,VALUE_LIMITCATALOG,PRODUCT',
            'Voucher.voucherValue' => 'required_if:Voucher.valueType,VALUE|numeric|min:0',
            'Voucher.reusable' => 'boolean',
            'Voucher.remainingValue' => 'nullable|numeric|min:0',
            'Product.id' => 'required_if:Voucher.valueType,PRODUCT|nullable|exists:products,id',
            'Category.id' => 'required_if:Voucher.valueType,VALUE_LIMITCATALOG|nullable|exists:categories,id',
            'Voucher.internalNotes' => 'nullable|string|max:1000',
        ]);                                           
        // Prepare voucher data
        $voucher = new Voucher();
        $voucher->create_mode = $data['Voucher']['createMode'];
        $voucher->codes_list = $data['Voucher']['codesList'] ?? null;
        $voucher->quantity = $data['Voucher']['quantity'] ?? 1;
        $voucher->issue_date = $data['Voucher']['issueDate'] ?? null;
        $voucher->expiry_date = $data['Voucher']['expiryDate'] ?? null;
        $voucher->travel_from_date = $data['Voucher']['travelFromDate'] ?? null;
        $voucher->travel_to_date = $data['Voucher']['travelToDate'] ?? null;
        $voucher->valid_redemption_days = $data['validRedemptionDaysIndex'] ?? [];
        $voucher->agent_id = $data['Voucher']['agent']['id'] ?? null;
        $voucher->internal_reference = $data['Voucher']['internalReference'] ?? null;
        $voucher->min_amount = $data['Voucher']['minAmount'] ?? null;
        $voucher->include_taxes_and_fees = $data['Voucher']['includeTaxesAndFees'] ?? 0;
        $voucher->include_extras = $data['Voucher']['includeExtras'] ?? 0;
        $voucher->value_type = $data['Voucher']['valueType'];
        $voucher->voucher_value = $data['Voucher']['voucherValue'] ?? null;
        $voucher->reusable = $data['Voucher']['reusable'] ?? 0;
        $voucher->remaining_value = $data['Voucher']['remainingValue'] ?? null;
        $voucher->product_id = $data['Product']['id'] ?? null;
        $voucher->category_id = $data['Category']['id'] ?? null;
        $voucher->internal_notes = $data['Voucher']['internalNotes'] ?? null;

        $voucher->save();

        return redirect()->route('admin.vouchers.index')
                         ->with('success', 'Voucher created successfully.');
    }

    // Edit form
    public function edit(Voucher $voucher)
    {
        $products = Product::all();
        $categories = Category::all();
        $agents = Agent::all();
        return view('admin.vouchers.edit', compact('voucher', 'products', 'categories', 'agents'));
    }

    // Update voucher
    public function update(Request $request, Voucher $voucher)
    {
        $data = $request->validate([
            'Voucher.createMode' => 'required|in:REZDY,MANUAL',
            'Voucher.codesList' => 'required_if:Voucher.createMode,MANUAL',
            'Voucher.quantity' => 'required_if:Voucher.createMode,REZDY|integer|min:1',
            'Voucher.issueDate' => 'nullable|date',
            'Voucher.expiryDate' => 'nullable|date|after_or_equal:Voucher.issueDate',
            'Voucher.travelFromDate' => 'nullable|date',
            'Voucher.travelToDate' => 'nullable|date|after_or_equal:Voucher.travelFromDate',
            'validRedemptionDaysIndex' => 'nullable|array',
            'validRedemptionDaysIndex.*' => 'integer|between:1,7',
            'Voucher.agent.id' => 'nullable|exists:agents,id',
            'Voucher.internalReference' => 'nullable|string|max:200',
            'Voucher.minAmount' => 'nullable|numeric|min:0',
            'Voucher.includeTaxesAndFees' => 'boolean',
            'Voucher.includeExtras' => 'boolean',
            'Voucher.valueType' => 'required|in:VALUE_LIMITPRODUCT,VALUE,VALUE_LIMITCATALOG,PRODUCT',
            'Voucher.voucherValue' => 'required_if:Voucher.valueType,VALUE|numeric|min:0',
            'Voucher.reusable' => 'boolean',
            'Voucher.remainingValue' => 'nullable|numeric|min:0',
            'Product.id' => 'required_if:Voucher.valueType,PRODUCT|nullable|exists:products,id',
            'Category.id' => 'required_if:Voucher.valueType,VALUE_LIMITCATALOG|nullable|exists:categories,id',
            'Voucher.internalNotes' => 'nullable|string|max:1000',
        ]);

        // Update voucher similarly
        $voucher->update([
            'create_mode' => $data['Voucher']['createMode'],
            'codes_list' => $data['Voucher']['codesList'] ?? null,
            'quantity' => $data['Voucher']['quantity'] ?? 1,
            'issue_date' => $data['Voucher']['issueDate'] ?? null,
            'expiry_date' => $data['Voucher']['expiryDate'] ?? null,
            'travel_from_date' => $data['Voucher']['travelFromDate'] ?? null,
            'travel_to_date' => $data['Voucher']['travelToDate'] ?? null,
            'valid_redemption_days' => $data['validRedemptionDaysIndex'] ?? [],
            'agent_id' => $data['Voucher']['agent']['id'] ?? null,
            'internal_reference' => $data['Voucher']['internalReference'] ?? null,
            'min_amount' => $data['Voucher']['minAmount'] ?? null,
            'include_taxes_and_fees' => $data['Voucher']['includeTaxesAndFees'] ?? 0,
            'include_extras' => $data['Voucher']['includeExtras'] ?? 0,
            'value_type' => $data['Voucher']['valueType'],
            'voucher_value' => $data['Voucher']['voucherValue'] ?? null,
            'reusable' => $data['Voucher']['reusable'] ?? 0,
            'remaining_value' => $data['Voucher']['remainingValue'] ?? null,
            'product_id' => $data['Product']['id'] ?? null,
            'category_id' => $data['Category']['id'] ?? null,
            'internal_notes' => $data['Voucher']['internalNotes'] ?? null,
        ]);

        return redirect()->route('admin.vouchers.index')
                         ->with('success', 'Voucher updated successfully.');
    }

    // Delete voucher
    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return redirect()->route('admin.vouchers.index')
                         ->with('success', 'Voucher deleted successfully.');
    }

    public function fetch_voucher($voucher)
    {
        $voucherData = Voucher::where('code', $voucher)->first();

        if (!$voucherData) {
            return response()->json([
                'status' => false,
                'message' => 'Voucher not found.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'voucher' => [
                'id' => $voucherData->id,
                'title' => $voucherData->title,
                'code' => $voucherData->code,
                'type' => $voucherData->type,
                'value' => $voucherData->value,
                'min_amount' => $voucherData->min_amount,
                'status_flag' => $voucherData->status,
                'start_date' => $voucherData->start_date,
                'end_date' => $voucherData->end_date,
                'max_uses' => $voucherData->max_uses,
                'used_count' => $voucherData->used_count,
                'agent' => $voucherData->agent,
                'category_id' => $voucherData->category_id,
                'last_text_input' => $voucherData->last_text_input,
            ]
        ]);
    }

}
