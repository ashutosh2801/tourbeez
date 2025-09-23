<?php

namespace App\Http\Controllers;

use App\Models\Tourtype;
use Illuminate\Http\Request;
use Str;

class TourTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Tourtype::orderBy('id','DESC')->get();
        return view('admin.tour_type.index',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tour_type.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'=>'required|max:255',
        ]);
        $baseSlug = Str::slug($request->name);
        $uniqueSlug = $baseSlug;
        $counter = 1;
        while (Tourtype::where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = $baseSlug . '-' . $counter;
            $counter++;
        }
        Tourtype::create([
            'name'=>$request->name,
            'slug'=>$uniqueSlug,
        ]);
        return redirect()->route('admin.tour_type.index')->with('success','Tour type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $data = Tourtype::where('id',decrypt($id))->first();
        return view('admin.tour_type.edit',compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data = Tourtype::where('id',decrypt($id))->first();
        return view('admin.tour_type.edit',compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'=>'required|max:255',
        ]);
        $baseSlug = Str::slug($request->name);
        $uniqueSlug = $baseSlug;
        $counter = 1;
        
        while (Tourtype::where('slug', $uniqueSlug)->where('id', '!=', $request->id)->exists()) {
            $uniqueSlug = $baseSlug . '-' . $counter;
            $counter++;
        }

        Tourtype::where('id', $request->id)->update([
            'name' => $request->name,
            'slug' => $uniqueSlug,
        ]);
        return redirect()->route('admin.tour_type.index')->with('success','Tour type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Tourtype::where('id',decrypt($id))->delete();
        return redirect()->route('admin.tour_type.index')->with('error','Category deleted successfully.');   
    }
}
