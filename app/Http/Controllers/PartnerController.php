<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $partners = Partner::when($search, function ($q) use ($search) {
            $q->where('name', 'like', "%$search%");
        })->latest()->paginate(15);

        return view('admin.partners.index', compact('partners', 'search'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'slug' => $request->slug
                ? Str::slug($request->slug)
                : Str::slug($request->name)
        ]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:150',
            'slug' => 'required|max:150|unique:partners,slug',
            'logo_url' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Partner::create([
            'name'      => $request->name,
            'slug'      => $request->slug,
            'upload_id' => $request->upload_id,
            'logo_url'  => $request->logo_url
        ]);

        return redirect()->route('admin.partners.index')
            ->with('success', 'Partner added successfully');
    }


    public function edit($id)
    {
        $partner = Partner::findOrFail($id);
        return view('admin.partners.edit', compact('partner'));
    }

    public function update(Request $request, $id)
    {

        $partner = Partner::findOrFail($id);

        $request->merge([
            'slug' => $request->slug
                ? Str::slug($request->slug)
                : Str::slug($request->name)
        ]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:150',
            'slug' => [
                'required',
                'max:150',
                Rule::unique('partners')->ignore($partner->id)
            ],
            'logo_url' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $partner->update([
            'name'      => $request->name,
            'slug'      => $request->slug,
            'upload_id' => $request->upload_id,
            'logo_url'  => $request->logo_url
        ]);

        return redirect()->route('admin.partners.index')
            ->with('success', 'Partner updated successfully');
    }

    public function destroy($id)
    {
        Partner::findOrFail($id)->delete();
        return back()->with('success', 'Partner deleted successfully');
    }
}
