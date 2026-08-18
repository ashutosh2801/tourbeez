<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryFaq;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $query = Category::with('tours');

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter categories that have tours
        if ($request->filled('has_tours')) {
            if ($request->has_tours == 1) {
                $query->has('tours');
            } elseif ($request->has_tours == 0) {
                $query->doesntHave('tours');
            }
        }

        $perPage = $request->input('per_page', 20);
        $perPage = in_array((string) $perPage, ['10', '20', '25', '50', '100'], true)
            ? (int) $perPage
            : 20;
        $data = $query->latest()->paginate($perPage)->withQueryString();
        return view('admin.category.index', compact('data'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'slug' => 'required|max:255',
        ]);

        $baseSlug = Str::slug($request->slug);
        $uniqueSlug = $baseSlug;
        $counter = 1;

        while (Category::where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $keywords = array_filter(array_map('trim', explode(',', $request->meta_keywords)));


        $category = Category::create([
            'name' => $request->name,
            'slug' => $uniqueSlug,
            'user_id' => auth()->id(),
            'meta_description' => $request->meta_description,
            'description' => $request->description,
            'meta_title' => $request->meta_title,
            'meta_keywords' => $keywords,
        ]);

        $category->update([
            'canonical_url' => url("{$uniqueSlug}/{$category->id}-c3")
        ]);

        return redirect()
            ->route('admin.category.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit($category)
    {
        $data = Category::with('faqs')->where('id',decrypt($category))->first();

        $tours = Tour::select('title','slug', 'id', 'unique_code')->get();
        
        $tour_category = $data->tours->pluck('id')->toArray();

        // dd($tour_categroy);

        return view('admin.category.edit',compact('data', 'tours', 'tour_category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {


        $request->validate([
            'name' => 'required|max:255',
            'slug' => 'required|max:255',
        ]);

        $category = Category::findOrFail($request->id);

        // Make slug unique (excluding current ID)
        $baseSlug = Str::slug($request->slug);
        $uniqueSlug = $baseSlug;
        $counter = 1;

        while (
            Category::where('slug', $uniqueSlug)
                ->where('id', '!=', $category->id)
                ->exists()
        ) {
            $uniqueSlug = $baseSlug . '-' . $counter;
            $counter++;
        }
         

        $keywords = array_filter(array_map('trim', explode(',', $request->meta_keywords)));

        $category->meta_keywords = array_map('trim', $keywords);
        $category->update([
            'name' => $request->name,
            'slug' => $uniqueSlug,
            'meta_description' => $request->meta_description,
            'description' => $request->description,
            'canonical_url' => url("{$uniqueSlug}/{$category->id}-c3"),
            'meta_title' => $request->meta_title,
            'meta_keywords' => $keywords,
        ]);

        $category->faqs()->delete();

        if ($request->questions) {
            foreach ($request->questions as $index => $question) {

                if (!empty($question) && !empty($request->answers[$index])) {

                    CategoryFaq::create([
                        'category_id' => $category->id,
                        'user_id' => auth()->id(),
                        'question' => $question,
                        'answer' => $request->answers[$index],
                    ]);
                }
            }
        }

        $tourIds = $request->tour;
        $category->tours()->sync($tourIds);
        return redirect()
            ->route('admin.category.index')
            ->with('info', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        Category::where('id',decrypt($id))->delete();
        return redirect()->route('admin.category.index')->with('error','Category deleted successfully.');   
    }

    public function clone($id)
    {
        $category = Category::with('tours')->findOrFail(decrypt($id));

        // Create new name
        $newName = $category->name . ' Copy';

        // Generate slug
        $baseSlug = Str::slug($newName);
        $uniqueSlug = $baseSlug;
        $counter = 1;

        while (Category::where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = $baseSlug . '-' . $counter;
            $counter++;
        }

        // Create new category
        $newCategory = Category::create([
            'name' => $newName,
            'slug' => $uniqueSlug,
            'user_id' => auth()->id(),
            'description' => $category->description,
            'meta_description' => $category->meta_description,
        ]);

        // Update canonical URL
        $newCategory->update([
            'canonical_url' => url("{$uniqueSlug}/{$newCategory->id}-c3")
        ]);

        // Attach tours
        $newCategory->tours()->attach(
            $category->tours->pluck('id')->toArray()
        );

        return redirect()
            ->route('admin.category.index')
            ->with('success', 'Category cloned successfully.');
    }
}
