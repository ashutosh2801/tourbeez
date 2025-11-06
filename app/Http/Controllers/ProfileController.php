<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Models\UserSupplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{

    public function dashboard()
    {
        return view('dashboard');
    }
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // dd($request->post());
        // dd($request->user());
        $request->user()->fill($request->validated());
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }
        User::where('id', $request->user()->id)->update(['mode'=>$request->mode]);

        $request->user()->save();

        return Redirect::route('admin.profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function suplierUpdate(Request $request)
    {

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
            ['user_id' => $request->user_id],
            $supplierData
        );

        return Redirect::route('admin.profile.edit')->with('status', 'profile-updated');
    }

    
}
