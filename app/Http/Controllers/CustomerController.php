<?php

namespace App\Http\Controllers;

use App\Models\OrderCustomer;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $roles = Role::all();
        view()->share('roles',$roles);
    }
    public function index()
    {
        // $data = User::where('user_type', 'Member')
        //     ->where('role', '<>', 'Super Admin')->where('role', '<>', 'Admin')->orderBy('id','DESC')->paginate(10);

       

    // Users
        $users = User::where('user_type', 'Member')
            ->whereNotIn('role', ['Super Admin', 'Admin'])
            ->get()
            ->map(function ($user) {
                return (object) [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'phonenumber' => $user->phone,
                    'source'      => 'user',
                    'created_at'  => $user->created_at,
                ];
            });

        // Order Customers
        $customers = OrderCustomer::get()
            ->map(function ($customer) {
                return (object) [
                    'id'          => $customer->id,
                    'name'        => trim($customer->first_name . ' ' . $customer->last_name),
                    'email'       => $customer->email,
                    'phonenumber' => $customer->phone,
                    'source'      => 'order_customer',
                    'created_at'  => $customer->created_at,
                ];
            });

        // Merge + unique email
        $merged = $users
            ->merge($customers)
            ->unique('email')
            ->sortByDesc('created_at')
            ->values();

        // Manual pagination
        $perPage = 10;
        $page = request()->get('page', 1);
        $data = new LengthAwarePaginator(
            $merged->forPage($page, $perPage),
            $merged->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        return view('admin.customer.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.user.create');
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

    public function editFromSource($id, $source)
    {
        $id = decrypt($id);

        if ($source === 'user') {
            $user = User::findOrFail($id);
        } elseif ($source === 'order_customer') {
            $user = OrderCustomer::findOrFail($id);
        } else {
            abort(404);
        }

        return view('admin.customer.edit', compact('user', 'source'));
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, $id)
    {

        if ($request->source === 'user') {
            return $this->updateUser($request, $id);
        }

        if ($request->source === 'order_customer') {
            return $this->updateOrderCustomer($request, $id);
        }

        abort(404);
    }
    public function updateSource(Request $request, $id)
    {
        
        if ($request->source === 'user') {
            return $this->updateUser($request, $id);
        }

        if ($request->source === 'order_customer') {
            
            return $this->updateOrderCustomer($request, $id);
        }


        abort(404);
    }


    protected function updateUser(Request $request, $id)
    {
        $user = User::findOrFail(decrypt($id));
        
        $user->update([
            'name'  => $request->oc_first_name ." " . $request->oc_last_name,
            'email' => $request->email,
            'phone' => $request->oc_phone ?? $user->phone,
        ]);

        // Optional: update linked order customer
        if ($user->customer) {
            $user->customer->update([
                'first_name'   => $request->oc_first_name,
                'last_name'    => $request->oc_last_name,
                'email'        => $request->oc_email,
                'phone'        => $request->oc_phone,
                // 'instructions' => $request->oc_instructions,
                // 'pickup_id'    => $request->oc_pickup_id,
                // 'pickup_name'  => $request->oc_pickup_name,
            ]);
        }

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'User updated successfully');
    }

    protected function updateOrderCustomer(Request $request, $id)
    {

        $customer = OrderCustomer::findOrFail(decrypt($id));

        $customer->update([
            'first_name'   => $request->oc_first_name,
            'last_name'    => $request->oc_last_name,
            'email'        => $request->oc_email,
            'phone'        => $request->oc_phone,
            // 'instructions' => $request->oc_instructions,
            // 'pickup_id'    => $request->oc_pickup_id,
            // 'pickup_name'  => $request->oc_pickup_name,
        ]);

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Customer updated successfully');
    }

    /**
     * 
     * 
     * 

     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail(decrypt($id));
        if ($user::destroy($id)) {
            return redirect()->route('admin.customers.index')->with('success', translate('Customers deleted successfully'));
        } else {
            return back()->with('error', translate('Sorry! Something went wrong.'));
        }
    }
}
