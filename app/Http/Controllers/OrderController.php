<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Tour;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $orders = Order::all();
        return view('admin.order.index', compact(['orders']));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $order = Order::findOrFail( decrypt($id) );
        $tours = Tour::orderBy('title', 'ASC')->get();
        return view('admin.order.edit', compact(['order', 'tours']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = $request->validate([
            'order_status'   => 'required|max:255',
        ],
        [
            'order_status.required'   => 'Please select order status',
        ]);
        
        $order = Order::findOrFail( $id );
        $order->order_status    = $request->order_status;
        $order->additional_info = $request->additional_info;
        if( !$order->save() )
        return redirect()->back()->withErrors($validator)->withInput()->with('error', 'Order has beend updated!');

        return redirect()->back()->withErrors($validator)->withInput()->with('success', 'Order has beend updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
