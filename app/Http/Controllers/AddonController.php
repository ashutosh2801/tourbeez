<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Str;

class AddonController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;

        // $category = Category::get();
        // view()->share('category', $category);

        // $tour_type = Tourtype::get();
        // view()->share('tour_type', $tour_type);

        // $data = Tour::orderBy('id', 'DESC')->get();
        // view()->share('data', $data);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Addon::orderBy('sort_order','ASC')->get();
        return view('admin.addon.index',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.addon.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|max:255',
            'price' => 'required|numeric',
            'description'   => 'required|string'
        ]);


        $image = '';
        if( $request->hasFile('image') ) { 
            // Generate unique image name
            $baseSlug = Str::slug($request->name);
            $uniqueSlug = $baseSlug . '-' . rand(10,99);
            $image = $this->imageService->compressAndStoreImage($request->file('image'), $uniqueSlug, 'addon');
            
        }

        Addon::create([
            'name'  => $request->name,
            'price' => $request->price,
            'customer_choice' => $request->customer_choice,
            'description' => $request->description,
            'availability' => $request->availability,
            'image' => $image
        ]);

        // $tourId = $tour->id;
        // if( $request->hasFile('image') ) { 
        //     $tour->image = $this->imageService->compressAndStoreImage($request->file('image'), $uniqueSlug, 'tour');
        //     $tour->save();
        // }
        
        return redirect()->route('admin.addon.index')->with('success','Addon created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Addon $addon)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data = Addon::findOrFail(decrypt($id));
        return view('admin.addon.edit',compact('data'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'id'            => 'required',
            'name'          => 'required|max:255',
            'price'         => 'required|numeric',
            'description'   => 'required|string'
        ]);

        $image = '';
        if( $request->hasFile('image') ) { 
            // Generate unique image name
            $baseSlug = Str::slug($request->name);
            $uniqueSlug = $baseSlug . '-' . rand(10,99);
            $image = $this->imageService->compressAndStoreImage($request->file('image'), $uniqueSlug, 'addon');
        }

        $addon = Addon::findOrFail($request->id);
        $addon->name            = $request->name;
        $addon->price           = $request->price;
        $addon->customer_choice = $request->customer_choice;
        $addon->description     = $request->description;
        $addon->availability    = $request->availability;
        $addon->image           = $image;
        if($addon->save()) {
            return redirect()->route('admin.addon.index')->with('success','Addon updated successfully.');
        }
        
        return back()->withInput()->withErrors($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Addon::where('id',decrypt($id))->delete();
        return redirect()->route('admin.addon.index')->with('error','Addon deleted successfully.');  
    }

    public function updateOrder(Request $request)
    {
        foreach ($request->rows as $row) {
            Addon::where('id', $row['id'])->update(['sort_order' => $row['order']]);
        }

        return response()->json(['status' => 'success']);
    }
}
