<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDriver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index()
    {
        $query = User::where('role', 'Driver');

        if ($name = request('name')) {
            $query->where('name', 'like', "%$name%");
        }

        if ($email = request('email')) {
            $query->where('email', 'like', "%$email%");
        }

        $data = $query->latest()->paginate(10);

        return view('admin.user.index', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'required',
            'license_number' => 'required',
        ]);

        // USER
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password ?? 'password123'),
            'role' => 'Driver',
            'user_type' => 'Driver',
        ]);

        // FILES
        $licenseFile = $request->file('license_file')?->store('drivers/license', 'public');
        $rcFile = $request->file('rc_file')?->store('drivers/rc', 'public');
        $insuranceFile = $request->file('insurance_file')?->store('drivers/insurance', 'public');

        // DRIVER DETAILS
        UserDriver::create([
            'user_id' => $user->id,
            'license_number' => $request->license_number,
            'license_expiry' => $request->license_expiry,
            'govt_id' => $request->govt_id,

            'vehicle_type' => $request->vehicle_type,
            'vehicle_number' => $request->vehicle_number,
            'vehicle_model' => $request->vehicle_model,
            'vehicle_capacity' => $request->vehicle_capacity,

            'license_file' => $licenseFile,
            'rc_file' => $rcFile,
            'insurance_file' => $insuranceFile,

            'per_day_rate' => $request->per_day_rate,
            'is_available' => $request->is_available ? 1 : 0,

            'city' => $request->city,
            'address' => $request->address,
        ]);

        return redirect()->route('admin.driver.index')
            ->with('success', 'Driver created successfully');
    }
}
