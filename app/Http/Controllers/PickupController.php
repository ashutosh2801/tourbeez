<?php

namespace App\Http\Controllers;

use App\Models\Pickup;
use App\Models\PickupLocation;
use Illuminate\Http\Request;

class PickupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Pickup::orderBy('sort_order','ASC')->get();
        return view('admin.pickup.index',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.pickup.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'                          => 'required|max:255',
            'PickupLocations'                => 'required|array',
            'PickupLocations.*.location'     => 'required|string|max:255',
            'PickupLocations.*.address'      => 'required|string|max:255',
            'PickupLocations.*.time'         => 'required|string|max:255',
        ],
        [
            'name.required'                     => 'Please enter a pickup name',
            'PickupLocations.*.location.required'=> 'Please enter location',
            'PickupLocations.*.address.required' => 'Please enter address',
            'PickupLocations.*.time.required'    => 'Please enter time',
        ]);

        // Update tour instance
        $pickup        = new Pickup();
        $pickup->name  = $request->name;
        $pickup->price  = $request->price;
        $pickup->pickup_charge  = $request->pickup_charge ?? 0;

        if( $pickup->save() ) {
            if ($request->has('PickupLocations') && is_array($request->PickupLocations)) {
                foreach ($request->PickupLocations as $option) {
                    $location = new PickupLocation();
                    $location->pickup_id  = $pickup->id;
                    $location->location   = $option['location'] ?? null;
                    $location->address    = $option['address'] ?? null;
                    $location->time       = $option['time'] ?? null;
                    $location->additional = $option['additional_information'] ?? null;
                    $location->save();
                }
            }

            return redirect()->route('admin.pickups.index')->with('success','Pickup locations created successfully.');
        }
        
        return redirect()->route('admin.pickups.index')->with('error','Something went wrong!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Pickup $pickup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data = Pickup::findOrFail(decrypt($id));
        return view('admin.pickup.edit',compact('data'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        $request->validate([
            'name'                           => 'required|max:255',
            'PickupLocations'                => 'required|array',
            'PickupLocations.*.location'     => 'required|string|max:255',
            'PickupLocations.*.address'      => 'required|string|max:255',
            'PickupLocations.*.time'         => 'required|string|max:255',
        ],
        [
            'name.required'                      => 'Please enter a pickup name',
            'PickupLocations.*.location.required'=> 'Please enter location',
            'PickupLocations.*.address.required' => 'Please enter address',
            'PickupLocations.*.time.required'    => 'Please enter time',
        ]);

        $pickup = Pickup::findOrFail(decrypt($id));

        $pickup->name           = $request->name;
        $pickup->price          = $request->price;
        $pickup->pickup_charge  = $request->pickup_charge;

        if($pickup->save()) {

            if ($request->has('PickupLocations') && is_array($request->PickupLocations)) {

                // Optional: delete old ones not in the list (if needed)
                $pickupIds = collect($request->PickupLocations)->pluck('id')->filter()->toArray();
                if( !empty($pricingIds) ) { 
                    $pickup->pricings()->whereNotIn('id', $pickupIds)->delete();
                }

                foreach ($request->PickupLocations as $option) {
                    // dd($option, $request->PickupLocations);
                    if (!empty($option['id'])) {
                        $pickupLocation = PickupLocation::find($option['id']);
                        
                        if ($pickupLocation && $pickupLocation->pickup_id == $pickup->id) {
                            $pickupLocation->location  = $option['location'] ?? null;
                            $pickupLocation->address   = $option['address'] ?? null;
                            $pickupLocation->time      = $option['time'] ?? null;
                            $pickupLocation->additional= $option['additional'] ?? null;
                            $pickupLocation->save();
                        }
                    } else {
                        $pickupLocation = new PickupLocation();
                        $pickupLocation->pickup_id     = $pickup->id ?? decrypt($id);
                        $pickupLocation->location      = $option['location'] ?? null;
                        $pickupLocation->address       = $option['address'] ?? null;
                        $pickupLocation->time          = $option['time'] ?? null;
                        $pickupLocation->additional    = $option['additional'] ?? null;
                        $pickupLocation->save();
                    }
                }
            }

            return redirect()->route('admin.pickups.index')->with('success','Pickup updated successfully.');
        }

        return back()->withInput()->withErrors($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Pickup::where('id',decrypt($id))->delete();
        return redirect()->route('admin.pickups.index')->with('error','Pickup deleted successfully.');  
    }

    public function updateOrder(Request $request)
    {
        foreach ($request->rows as $row) {
            Pickup::where('id', $row['id'])->update(['sort_order' => $row['order']]);
        }

        return response()->json(['status' => 'success']);
    }
}
