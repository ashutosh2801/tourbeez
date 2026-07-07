<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Display a listing of the vehicles.
     */
    public function index()
    {
        $code = request('code');
        $name = request('name');

        $query = Vehicle::query();

        if ($code) {
            $query->where('code', 'like', '%' . $code . '%');
        }

        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }

        $perPage = request('per_page', 10);

        $data = $query
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        return view('admin.vehicle.index', compact('data'));
    }

    /**
     * Show the form for creating a new vehicle.
     */
    public function create()
    {
        return view('admin.vehicle.create');
    }

    /**
     * Store a newly created vehicle.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code'        => 'required|max:55|unique:vehicles,code',
            'name'        => 'required|max:255',
            'no_of_seats' => 'nullable|integer|min:1',
        ]);

        Vehicle::create([
            'code'        => $request->code,
            'name'        => $request->name,
            'no_of_seats' => $request->no_of_seats,
        ]);

        return redirect()
            ->route('admin.vehicles.index')
            ->with('success', 'Vehicle created successfully.');
    }

    /**
     * Display the specified vehicle.
     */
    public function show(string $id)
    {
        $vehicle = Vehicle::findOrFail(decrypt($id));

        return view('admin.vehicle.show', compact('vehicle'));
    }

    /**
     * Show the form for editing the specified vehicle.
     */
    public function edit(string $id)
    {
        $vehicle = Vehicle::findOrFail(decrypt($id));

        return view('admin.vehicle.edit', compact('vehicle'));
    }

    /**
     * Update the specified vehicle.
     */
    public function update(Request $request, string $id)
    {
        $vehicle = Vehicle::findOrFail(decrypt($id));

        $request->validate([
            'code'        => 'required|max:55|unique:vehicles,code,' . $vehicle->id,
            'name'        => 'required|max:255',
            'no_of_seats' => 'nullable|integer|min:1',
        ]);

        $vehicle->update([
            'code'        => $request->code,
            'name'        => $request->name,
            'no_of_seats' => $request->no_of_seats,
        ]);

        return redirect()
            ->route('admin.vehicles.index')
            ->with('success', 'Vehicle updated successfully.');
    }

    /**
     * Remove the specified vehicle.
     */
    public function destroy(string $id)
    {
        $vehicle = Vehicle::findOrFail(decrypt($id));

        $vehicle->delete();

        return redirect()
            ->route('admin.vehicles.index')
            ->with('success', 'Vehicle deleted successfully.');
    }
}