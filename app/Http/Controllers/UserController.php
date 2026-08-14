<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDriver;
use App\Models\UserSupplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $roles = Role::all();
        view()->share('roles',$roles);
    }

    public function index()
    {
        $query = User::whereNotIn('user_type', ['Member', 'driver'])
            ->whereNotIn('role', ['Super Admin', 'Admin']);

        // Filters
        if ($name = request('name')) {
            $query->where('name', 'like', "%{$name}%");
        }

        if ($email = request('email')) {
            $query->where('email', 'like', "%{$email}%");
        }

        // Pagination count
        $perPage = request('per_page', 10);

        $data = $query->orderBy('id', 'DESC')
            ->paginate($perPage)
            ->withQueryString(); // keeps filters in pagination

        return view('admin.user.index', compact('data'));
    }

    public function create()
    {
        return view('admin.user.create');
    }

    public function store(Request $request)
    {
        // 🧩 1️⃣ Validation
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:17'],
            'role' => ['required', 'string'],
        ]);

        // 🧩 2️⃣ Decide user_type
        $userType = 'staff';
        if ($request->role === 'Driver') {
            $userType = 'driver';
        } elseif ($request->role === 'Supplier') {
            $userType = 'supplier';
        }

        // 🧩 3️⃣ Create User
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->first_name . ' ' . $request->last_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'user_type' => $userType,
            'email_notification' => $request->has('email_notification'),
            'text_notification' => $request->has('text_notification'),
        ]);

        $user->assignRole($request->role);

        // ================= SUPPLIER =================
        if ($request->role === 'Supplier') {

            $supplierData = [
                'user_id' => $user->id,
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

            if ($request->hasFile('license_file')) {
                $supplierData['license_file'] = $request->file('license_file')->store('uploads/licenses', 'public');
            }

            if ($request->hasFile('company_logo')) {
                $supplierData['company_logo'] = $request->file('company_logo')->store('uploads/logos', 'public');
            }

            UserSupplier::create($supplierData);
        }

        // ================= DRIVER =================
        if ($request->role === 'Driver') {

            $driverData = [
                'user_id'          => $user->id,
                'license_number'   => $request->license_number,
                'license_expiry'   => $request->license_expiry,
                'govt_id'          => $request->govt_id,

                'vehicle_type'     => $request->vehicle_type,
                'vehicle_number'   => $request->vehicle_number,
                'vehicle_model'    => $request->vehicle_model,
                'vehicle_capacity' => $request->vehicle_capacity,

                'per_day_rate'     => $request->per_day_rate,
                'is_available'     => $request->has('is_available') ? 1 : 0,

                'city'             => $request->city,
                'address'          => $request->address,
            ];

            // FILES
            if ($request->hasFile('license_file')) {
                $driverData['license_file'] = $request->file('license_file')->store('drivers/license', 'public');
            }

            if ($request->hasFile('rc_file')) {
                $driverData['rc_file'] = $request->file('rc_file')->store('drivers/rc', 'public');
            }

            if ($request->hasFile('insurance_file')) {
                $driverData['insurance_file'] = $request->file('insurance_file')->store('drivers/insurance', 'public');
            }

            UserDriver::create($driverData);
        }

        return redirect()->route('admin.user.index')
            ->with('success', 'User  ('.ucfirst($request->role).') created successfully.');
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'phone' => ['required', 'string', 'max:17'],
            'role' => ['required', 'string'],
        ]);

        // 🧩 2️⃣ Update User table
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->name = $request->first_name . ' ' . $request->last_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->role = $request->role;
        $user->email_notification = $request->has('email_notification');
        $user->text_notification = $request->has('text_notification');

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->save();
        $user->assignRole($request->role);

        // 🧩 3️⃣ Handle Supplier data (user_suppliers table)
        // ================= SUPPLIER =================
        if ($request->role === 'Supplier') {

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

            if ($request->hasFile('license_file')) {
                $supplierData['license_file'] = $request->file('license_file')->store('uploads/licenses', 'public');
            }

            if ($request->hasFile('company_logo')) {
                $supplierData['company_logo'] = $request->file('company_logo')->store('uploads/logos', 'public');
            }

            UserSupplier::updateOrCreate(
                ['user_id' => $user->id],
                $supplierData
            );
        }


        // ================= DRIVER =================
        if ($request->role === 'Driver') {

            $driverData = [
                'license_number'   => $request->license_number,
                'license_expiry'   => $request->license_expiry,
                'govt_id'          => $request->govt_id,

                'vehicle_type'     => $request->vehicle_type,
                'vehicle_number'   => $request->vehicle_number,
                'vehicle_model'    => $request->vehicle_model,
                'vehicle_capacity' => $request->vehicle_capacity,

                'per_day_rate'     => $request->per_day_rate,
                'is_available'     => $request->has('is_available') ? 1 : 0,

                'city'             => $request->city,
                'address'          => $request->address,
            ];

            // FILES
            if ($request->hasFile('license_file')) {
                $driverData['license_file'] = $request->file('license_file')->store('drivers/license', 'public');
            }

            if ($request->hasFile('rc_file')) {
                $driverData['rc_file'] = $request->file('rc_file')->store('drivers/rc', 'public');
            }

            if ($request->hasFile('insurance_file')) {
                $driverData['insurance_file'] = $request->file('insurance_file')->store('drivers/insurance', 'public');
            }

            UserDriver::updateOrCreate(
                ['user_id' => $user->id],
                $driverData
            );
        }

        return redirect()->back()
            // ->route('admin.user.index')
            ->with('success', 'User ('.ucfirst($request->role).') details updated successfully.');
    }


    public function destroy($id)
    {
        User::where('id',decrypt($id))->delete();
        return redirect()->back()->with('success','User deleted successfully.');
    }
}
