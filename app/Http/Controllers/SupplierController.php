<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    public function showForm()
    {
        return view('supplier.registration');
    }

    public function index()
    {

         $data = User::where('role', 'Supplier')->where('role', '<>', 'Super Admin')->orderBy('id','DESC')->get();   
        
        // $data = User::where('role', '<>', 'Super Admin')->orderBy('id','DESC')->get();
        return view('admin.user.index', compact('data'));
    }

    public function store(Request $request)
    {
        // Step 1–4 basic details validation
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required',
            'business_name' => 'required|string|max:255',
            'supplier_type' => 'required',
            'consent_info' => 'accepted',
            'consent_terms' => 'accepted',
        ]);

        // Create User
        $user = User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'password'   => Hash::make($request->password ?? 'password123'),
            'role'       => 'Supplier',
            'user_type'  => 'Supplier',
            'country'    => $request->country,
        ]);

        // Handle file uploads
        $licenseFile = $request->hasFile('license_file') ? 
            $request->file('license_file')->store('suppliers/licenses', 'public') : null;

        $logoFile = $request->hasFile('company_logo') ? 
            $request->file('company_logo')->store('suppliers/logos', 'public') : null;
        $serviceImages = [];
        if ($request->hasFile('service_images')) {
            foreach ($request->file('service_images') as $image) {
                $serviceImages[] = $image->store('suppliers/services', 'public');
            }
        }

        // Create Supplier Details
        UserSupplier::create([
            'user_id' => $user->id,
            'business_name' => $request->business_name,
            'supplier_type' => $request->supplier_type,
            'business_registration_number' => $request->business_registration_number,
            'year_established' => $request->year_established,
            'website_url' => $request->website_url,
            'social_links' => json_encode($request->social_links),

            'designation' => $request->designation,
            'secondary_contact' => $request->secondary_contact,

            'address' => $request->address,
            'operating_locations' => $request->operating_locations,

            'insurance_details' => $request->insurance_details,
            'license_file' => $licenseFile,
            'certifications' => json_encode($request->certifications),

            'payment_method' => $request->payment_method,
            'bank_details' => $request->bank_details,
            'currency' => $request->currency,

            'company_logo' => $logoFile,
            'service_images' => json_encode($serviceImages),
            'promotional_offers' => $request->promotional_offers,

            'consent_info' => $request->consent_info ? 1 : 0,
            'consent_terms' => $request->consent_terms ? 1 : 0,
            'digital_signature' => $request->digital_signature,
            'submitted_date' => now()->toDateString(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Supplier registered successfully!');
    }
}

