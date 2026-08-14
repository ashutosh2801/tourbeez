<?php

namespace App\Http\Controllers;

use App\Models\BusinessExpense;
use App\Models\Partner;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $expense_date = request('expense_date');
        $category     = request('category');
        $partner_id   = request('partner_id');
        $tour_id      = request('tour_id');
        $vendor       = request('vendor');

        $query = BusinessExpense::with(['partner', 'tour']);

        if ($expense_date) {
            $query->whereDate('expense_date', $expense_date);
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($partner_id) {
            $query->where('partner_id', $partner_id);
        }

        if ($tour_id) {
            $query->where('tour_id', $tour_id);
        }

        if ($vendor) {
            $query->where('vendor', 'like', '%' . $vendor . '%');
        }

        $perPage = request('per_page', 10);

        $data = $query
            ->latest()
            ->paginate($perPage)
            ->appends(request()->query());

        $partners = Partner::orderBy('name')->get();
        $tours = Tour::orderBy('title')->get();
        $categories = business_expense_categories();

        return view('admin.business-expense.index', compact(
            'data',
            'partners',
            'tours',
            'categories'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $partners = Partner::orderBy('name')->get();
        $tours = Tour::orderBy('title')->get();
        $categories = business_expense_categories();

        return view('admin.business-expense.create', compact(
            'partners',
            'tours',
            'categories'
        ));
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request)
    {
        $request->validate([
            'expense_date' => 'required|date',
            'category'     => 'required|max:100',
            'amount'       => 'required|numeric|min:0',
            'tour_id'      => 'nullable|exists:tours,id',
            'partner_id'   => 'nullable|exists:partners,id',
            'vendor'       => 'nullable|max:255',
            'description'  => 'nullable',
        ]);

        BusinessExpense::create([
            'expense_date' => $request->expense_date,
            'category'     => $request->category,
            'amount'       => $request->amount,
            'tour_id'      => $request->tour_id,
            'partner_id'   => $request->partner_id,
            'vendor'       => $request->vendor,
            'description'  => $request->description,
            'created_by'   => Auth::id(),
        ]);

        return redirect()
            ->route('admin.business-expenses.index')
            ->with('success', 'Business expense created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $businessExpense = BusinessExpense::with([
            'partner',
            'tour',
            'user'
        ])->findOrFail(decrypt($id));

        return view('admin.business-expense.show', compact('businessExpense'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $businessExpense = BusinessExpense::findOrFail(decrypt($id));

        $partners = Partner::orderBy('name')->get();
        $tours = Tour::orderBy('title')->get();
        $categories = business_expense_categories();

        return view('admin.business-expense.edit', compact(
            'businessExpense',
            'partners',
            'tours',
            'categories'
        ));
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, string $id)
    {
        $businessExpense = BusinessExpense::findOrFail(decrypt($id));

        $request->validate([
            'expense_date' => 'required|date',
            'category'     => 'required|max:100',
            'amount'       => 'required|numeric|min:0',
            'tour_id'      => 'nullable|exists:tours,id',
            'partner_id'   => 'nullable|exists:partners,id',
            'vendor'       => 'nullable|max:255',
            'description'  => 'nullable',
        ]);

        $businessExpense->update([
            'expense_date' => $request->expense_date,
            'category'     => $request->category,
            'amount'       => $request->amount,
            'tour_id'      => $request->tour_id,
            'partner_id'   => $request->partner_id,
            'vendor'       => $request->vendor,
            'description'  => $request->description,
        ]);

        return redirect()
            ->route('admin.business-expenses.index')
            ->with('success', 'Business expense updated successfully.');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(string $id)
    {
        $businessExpense = BusinessExpense::findOrFail(decrypt($id));

        $businessExpense->delete();

        return redirect()
            ->route('admin.business-expenses.index')
            ->with('success', 'Business expense deleted successfully.');
    }
}