<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Country;
use Redirect;
use Validator;

class CountryController extends Controller
{
    public function __construct()
    {
        //$this->middleware(['permission:show_countries'])->only('index');
        //$this->middleware(['permission:edit_country'])->only('updateStatus');

        $this->country_rules = [
            'name' => ['required','max:255', 'unique:countries'],
            'code' => ['required','max:3'],
        ];

        $this->country_messages = [
            'name.required'             => translate('Country Name is required'),
            'name.unique'             => translate('Country Name is already created'),
            'name.max'                  => translate('Max 255 characters'),
            'code.required'             => translate('Country Code is required'),
        ];
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search  = null;
        $countries    = Country::orderBy('id','asc');

        if ($request->has('search')){
            $sort_search  = $request->search;
            $countries    = $countries->where('name', 'like', '%'.$sort_search.'%');
        }
        $countries = $countries->paginate(10);
        return view('admin.attributes.countries.index', compact('countries','sort_search'));

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
        $rules      = $this->country_rules;
        $messages   = $this->country_messages;
        $validator  = Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            //flash(translate('Sorry! Something went wrong'))->error();
            return Redirect::back()->withInput()->withErrors($validator)->with('error',translate('Sorry! Something went wrong'));
        }
    
        $country       = new Country;
        $country->name = $request->name;
        $country->iso3 = $request->code;
        if($country->save()){
            //flash(translate('New country has been added successfully'))->success();
            return redirect()->route('admin.countries.index')->with('success', 'New country has been added successfully');
        } else {
            //flash(translate('Sorry! Something went wrong.'))->error();
            return back()->with('error','Something went wrong!');
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
        $country = Country::findOrFail(decrypt($id));
        return view('admin.attributes.countries.edit',compact('country'));
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
        $rules = $this->country_rules;
        $messages = $this->country_messages;
        $validator = Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            //flash(translate('Sorry! Something went wrong'))->error();
            return Redirect::back()->withErrors($validator)->with('success', translate('Sorry! Something went wrong.'));
        }
    
        $country       = Country::findOrFail($id);
        $country->name = $request->name;
        $country->iso3 = $request->code;
        if($country->save()){
            //flash(translate('Country info updated successfully.'))->success();
            return redirect()->route('admin.countries.index')->with('success', translate('Country info updated successfully.'));
        } else {
            //flash(translate('Sorry! Something went wrong.'))->error();
            return back()->with('error', translate('Sorry! Something went wrong.'));;
        }
    }

    public function updateStatus(Request $request){
        $country = Country::findOrFail($request->id);
        $country->status = $request->status;
        if($country->save()){
            return 1;
        }
        return 0;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $country = Country::findOrFail($id);
        foreach ($country->states as $key => $state) {
            foreach ($state->cities as $key => $city) {
                $city->delete();
            }
            $state->delete();
        }
        if (Country::destroy($id)) {
            //flash(translate('Country deleted successfully'))->success();
            return redirect()->route('admin.countries.index')->with('success', translate('Country deleted successfully'));
        } else {
            //flash(translate('Sorry! Something went wrong.'))->error();
            return back()->with('error', translate('Sorry! Something went wrong.'));
        }
    }
}
