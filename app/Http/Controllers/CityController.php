<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Redirect;
use Validator;

class CityController extends Controller
{
    public $city_rules = [];
    public $city_messages = [];

    public function __construct()
    {
        // $this->middleware(['permission:show_member_languages'])->only('index');
        // $this->middleware(['permission:edit_member_language'])->only('edit');
        // $this->middleware(['permission:delete_member_language'])->only('destroy');

        $this->city_rules = [
            'name' => ['required','max:255'],
            'state_id' => ['required'],
        ];

        $this->city_messages = [
            'name.required'             => translate('City Name is required'),
            'name.max'                  => translate('Max 255 characters'),
            'state_id.required'         => translate('State is required'),
        ];
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        $sort_search = $request->search;

        $cities = City::query()
            ->when($sort_search, function ($q) use ($sort_search) {
                $q->where('name', 'like', '%' . $sort_search . '%');
            })

            // Cities with image
            ->when($request->has_image, function ($q) {
                $q->whereNotNull('upload_id');
            })

            // Cities having tours
            ->when($request->has_tour, function ($q) {
                $q->whereHas('tours');
            })

            // Has latitude & longitude
            ->when($request->has_latlong, function ($q) {
                $q->whereNotNull('latitude')
                  ->whereNotNull('longitude');
            })
            ->orderByRaw('CASE WHEN `order` = 0 THEN 1 ELSE 0 END')
            ->orderBy('order', 'asc')
            ->paginate(10)
            ->appends($request->query());

        $states    = State::all();
        $countries = Country::where('status', 1)->get();

        return view(
            'admin.attributes.cities.index',
            compact('cities', 'states', 'countries', 'sort_search')
        );
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules      = $this->city_rules;
        $messages   = $this->city_messages;
        $validator  = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->with('error', translate('Sorry! Something went wrong'));
        }

        $city              = new City;
        $city->name        = $request->name;
        $city->state_id    = $request->state_id;
        $city->upload_id   = $request->upload_id;
        $city->latitude    = $request->latitude;
        $city->longitude   = $request->longitude;
        if($city->save())
        {
            return redirect()->route('admin.cities.index')->with('error', 'New City has been added successfully');
        }
        else {
            return back()->with(translate('Sorry! Something went wrong.'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $city        = city::findOrFail(decrypt($id));
        $countries   = Country::where('status',1)->get();
        return view('admin.attributes.cities.edit', compact('city','countries'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $rules      = $this->city_rules;
        $messages   = $this->city_messages;
        $validator  = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->with('error', translate('Sorry! Something went wrong'));
        }

        $city              = City::findOrFail($id);
        $city->name        = $request->name;
        $city->state_id    = $request->state_id;
        $city->upload_id   = $request->upload_id;
        $city->latitude    = $request->latitude;
        $city->longitude   = $request->longitude;
        if($city->save())
        {
            return redirect()->route('admin.cities.edit', encrypt($city->id))->with('success', translate('City info has been updated successfully'));
        }
        else {
            return back()->with('error', translate('Sorry! Something went wrong.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (City::destroy($id)) {
            //flash(translate('City info has been deleted successfully'))->success();
            return redirect()->route('admin.cities.index')->with('success', translate('City info has been deleted successfully'));
        } else {
            //flash(translate('Sorry! Something went wrong.'))->error();
            return back()->with('error', translate('Sorry! Something went wrong.'));
        }
    }

    // Get Cities by State
    public function get_cities_by_state(Request $request)
    {
        $cities = City::where('state_id', $request->state_id)->get();
        return $cities;
    }

    public function updateOrder(Request $request)
    {
        if ($request->orders) {
            foreach ($request->orders as $cityId => $order) {
                City::where('id', $cityId)->update([
                    'order' => (int) $order
                ]);
            }
        }

        return redirect()->back()->with('success', 'Order updated successfully.');
    }
}
