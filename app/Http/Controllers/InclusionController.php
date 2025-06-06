<?php

namespace App\Http\Controllers;

use App\Models\Inclusion;
use Illuminate\Http\Request;

class InclusionController extends Controller
{
    public function single(Request $request)
    {
        if( isset($request->feature_id) ) {
            return Inclusion::find($request->feature_id);
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
    public function show(Inclusion $inclusion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inclusion $inclusion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inclusion $inclusion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inclusion $inclusion)
    {
        //
    }
}
