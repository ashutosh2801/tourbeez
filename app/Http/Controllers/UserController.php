<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use App\Models\UserSupplier;

class UserController extends Controller
{
    public function __construct()
    {
        $roles = Role::all();
        view()->share('roles',$roles);
    }

    public function index()
    {

         $data = User::where('user_type', '!=', 'Member')
            ->where('role', '!=', 'Supplier')->where('role', '<>', 'Super Admin')->orderBy('id','DESC')->get();
            
        
        // $data = User::where('role', '<>', 'Super Admin')->orderBy('id','DESC')->get();
        return view('admin.user.index', compact('data'));
    }

    public function create()
    {
        return view('admin.user.create');
    }

    public function store(Request $request)
    {
        
        $request->validate([
            'name' => 'required', 'string', 'max:255',
            'email' => 'required', 'string', 'email', 'max:255', 'unique:'.User::class,
            'password' => 'required|max:255|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'user_type' => 'staff',
            'email_notification' => $request->has('email_notification'),
            'text_notification' => $request->has('text_notification')
        ]);
        $user->assignRole($request->role);
        return redirect()->route('admin.user.index')->with('success','User created successfully.');
    }

    public function edit($id)
    {
        $user = User::where('id',decrypt($id))->first();
        return view('admin.user.edit',compact('user'));
    }
    


    public function update(Request $request, User $user)
    {
        // 🧩 1️⃣ Validate basic user fields
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
        ]);

        // 🧩 2️⃣ Update User table
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->email_notification = $request->has('email_notification');
        $user->text_notification = $request->has('text_notification');

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->save();
        $user->assignRole($request->role);

        // 🧩 3️⃣ Handle Supplier data (user_suppliers table)
        $supplierData = [
            'business_name' => $request->business_name,
            'supplier_type' => $request->supplier_type,
            'business_registration_number' => $request->business_registration_number,
            'year_established' => $request->year_established,
            'website_url' => $request->website_url,
            'social_links' => $request->social_links,
            'designation' => $request->designation,
            'secondary_contact' => $request->secondary_contact,
            'address' => $request->address,
            'operating_locations' => $request->operating_locations,
            'insurance_details' => $request->insurance_details,
            'certifications' => $request->certifications,
            'payment_method' => $request->payment_method,
            'bank_details' => $request->bank_details,
            'currency' => $request->currency,
            'service_images' => $request->service_images,
            'promotional_offers' => $request->promotional_offers,
            'consent_info' => $request->has('consent_info') ? 1 : 0,
            'consent_terms' => $request->has('consent_terms') ? 1 : 0,
            'digital_signature' => $request->digital_signature,
            'submitted_date' => $request->submitted_date,
        ];

        // 🧩 4️⃣ Handle file uploads
        if ($request->hasFile('license_file')) {
            $path = $request->file('license_file')->store('uploads/licenses', 'public');
            $supplierData['license_file'] = $path;
        }

        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('uploads/logos', 'public');
            $supplierData['company_logo'] = $path;
        }

        // 🧩 5️⃣ Update or create the supplier record
        UserSupplier::updateOrCreate(
            ['user_id' => $user->id],
            $supplierData
        );

        return redirect()->back()
            // ->route('admin.user.index')
            ->with('success', 'User and supplier details updated successfully.');
    }


    public function destroy($id)
    {
        User::where('id',decrypt($id))->delete();
        return redirect()->back()->with('success','User deleted successfully.');
    }
}
