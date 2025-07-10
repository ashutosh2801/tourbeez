<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Cache::remember('category_list', 86400, function () {
            return Category::orderBy('name','DESC')->get();
        });

        return response()->json(['status' => true, 'data' => $data], 200);
    }

    public function subcategory(Request $request)
    {
        $data = Cache::remember('sub_category_list', 86400, function () {
            return SubCategory::orderBy('name','DESC')->get();
        });

        if($request->id !== null)
        $data = $data->where('category_id', $request->id);

        return response()->json(['status' => true, 'data' => $data], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //return view('admin.category.create');
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
        while (Category::where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = $baseSlug . '-' . $counter;
            $counter++;
        }
        Category::create([
            'name'=>$request->name,
            'slug'=>$uniqueSlug,
        ]);
        return redirect()->route('admin.category.index')->with('success','Category created successfully.');
    }

    public function edit($category)
    {
        $data = Category::where('id',decrypt($category))->first();
        return view('admin.category.edit',compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'name'=>'required|max:255',
        ]);
        $baseSlug = Str::slug($request->name);
        $uniqueSlug = $baseSlug;
        $counter = 1;
        
        while (Category::where('slug', $uniqueSlug)->where('id', '!=', $request->id)->exists()) {
            $uniqueSlug = $baseSlug . '-' . $counter;
            $counter++;
        }

        Category::where('id', $request->id)->update([
            'name' => $request->name,
            'slug' => $uniqueSlug,
        ]);
        return redirect()->route('admin.category.index')->with('info','Category updated successfully.');   
    }

    public function destroy($id)
    {
        Category::where('id',decrypt($id))->delete();
        return redirect()->route('admin.category.index')->with('error','Category deleted successfully.');   
    }
}
