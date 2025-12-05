<?php

namespace App\Http\Controllers;

use App\Models\OrderCustomer;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = User::where('user_type', 'Member')
            ->where('role', '<>', 'Super Admin')->orderBy('id','DESC')->get();

       
        return view('admin.customer.index', compact('data'));
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
    public function show(string $id)
    {
        $user = OrderCustomer::findOrFail(decrypt($id) );
        return view('admin.customer.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail(decrypt($id) );
        $orderCustomer = User::findOrFail(decrypt($id) );
        
        return view('admin.customer.edit', compact('user', 'orderCustomer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        if ($user::destroy($id)) {
            return redirect()->route('admin.customers.index')->with('success', translate('Customers deleted successfully'));
        } else {
            return back()->with('error', translate('Sorry! Something went wrong.'));
        }
    }
}
