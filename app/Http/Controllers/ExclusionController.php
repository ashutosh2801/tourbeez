<?php

namespace App\Http\Controllers;

use App\Models\Exclusion;
use Illuminate\Http\Request;

class ExclusionController extends Controller
{
    public function single(Request $request)
    {
        if( isset($request->feature_id) ) {
            return Exclusion::find($request->feature_id);
        }
        return null;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
    public function show(Exclusion $exclusion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Exclusion $exclusion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Exclusion $exclusion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exclusion $exclusion)
    {
        //
    }
}
