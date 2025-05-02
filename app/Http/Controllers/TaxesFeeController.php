<?php

namespace App\Http\Controllers;

use App\Models\TaxesFee;
use Illuminate\Http\Request;

class TaxesFeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = TaxesFee::orderBy('sort_order','DESC')->get();
        return view('admin.taxes.index',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.taxes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'label'         => 'required|string|max:255',
            'tax_fee_type'  => 'required|string|max:3',
            'fee_type'      => 'required_if:tax_fee_type,FEE|string|max:100',
            'tax_fee_value' => 'required',
            
        ],
        [
            'label.required' => 'Label is required!',
            'tax_fee_type.required' => 'Taxes/Fee is required!',
            'fee_type.required_if:tax_fee_type,FEE' => 'Value is required!',
            'tax_fee_value.required' => 'Percent/Tax amount is required!',
        ]);

        $taxfee = new TaxesFee();
        $taxfee->label =  $request->label;
        $taxfee->tax_fee_type = $request->tax_fee_type;
        $taxfee->fee_type = $request->fee_type;
        $taxfee->tax_fee_value = $request->tax_fee_value;
        if( $taxfee->save() ) {
            return redirect()->route('admin.taxes.index')->with('success','Taxes and Fees saved successfully.');
        }

        return redirect()->route('admin.taxes.index')->with('error','Something went wrong!');
    }

    /**
     * Display the specified resource.
     */
    public function show(TaxesFee $taxesFee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $taxfee = TaxesFee::findOrFail(decrypt($id));
        return view('admin.taxes.edit', compact('taxfee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'label'         => 'required|string|max:255',
            'tax_fee_type'  => 'required|string|max:3',
            'fee_type'      => 'required_if:tax_fee_type,FEE|string|max:100',
            'tax_fee_value' => 'required',
            
        ],
        [
            'label.required' => 'Label is required!',
            'tax_fee_type.required' => 'Taxes/Fee is required!',
            'fee_type.required_if:tax_fee_type,FEE' => 'Value is required!',
            'tax_fee_value.required' => 'Percent/Tax amount is required!',
        ]);

        $taxfee = TaxesFee::findOrFail($id);
        $taxfee->label =  $request->label;
        $taxfee->tax_fee_type = $request->tax_fee_type;
        $taxfee->fee_type = $request->fee_type;
        $taxfee->tax_fee_value = $request->tax_fee_value;
        if( $taxfee->save() ) {
            return redirect()->route('admin.taxes.index')->with('success','Taxes and Fees saved successfully.');
        }

        return redirect()->route('admin.taxes.index')->with('error','Something went wrong!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaxesFee $taxesFee)
    {
        //
    }

    public function updateOrder(Request $request)
    {
        foreach ($request->rows as $row) {
            TaxesFee::where('id', $row['id'])->update(['sort_order' => $row['order']]);
        }

        return response()->json(['status' => 'success']);
    }
}
