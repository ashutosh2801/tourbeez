<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use Cache;
use Illuminate\Http\Request;

class TourController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = Cache::remember('tour_list', 86400, function () {
            return Tour::Where('status', 1)->whereNull('deleted_at')->orderBy('id','DESC')->get();
        });

        if($request->title)
        $data = $data->where('title', 'like', '%' . $request->title . '%');
        
        if($request->category_id)
        $data = $data->where('category_id', $request->category_id);
        
        if($request->sub_category_id)
        $data = $data->where('sub_category_id', $request->sub_category_id);        

        return response()->json(['status' => true, 'data' => $data], 200);
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
    public function show(string $id)
    {
        $data = Tour::find($id);
        if ($data) {
            return response()->json(['status' => true, 'data' => $data], 200);
        } else {
            return response()->json(['status' => false, 'message' => 'Tour not found'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
