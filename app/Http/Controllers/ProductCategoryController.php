<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use App\Models\Account;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        // Start query
        $query = ProductCategory::orderBy('name');

        // Filter by status
        if ($request->filled('status')) {
            $isActive = $request->status === 'active' ? 1 : 0;
            $query->where('is_active', $isActive);
        }

        // Handle invalid date range
        if ($request->filled('from_date') && $request->filled('to_date') && $request->to_date < $request->from_date) {
            $categories = new LengthAwarePaginator([], 0, 15);
            return view('products.categories.index', compact('categories'))
                ->withErrors(['to_date' => 'The "To Date" must be greater than or equal to the "From Date".']);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Paginate results
        $categories = $query->simplePaginate(15)->appends($request->all());

        return view('products.categories.index', compact('categories'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:product_categories,name',
        ]);

        ProductCategory::create([
            'name'   => $validated['name'],
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return back()->with('success', 'Category added successfully.');
    }

    public function update(Request $request, ProductCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:product_categories,name,' . $category->id,
        ]);

        $category->update([
            'name'   => $validated['name'],
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return back()->with('success', 'Category updated successfully.');
    }

    public function destroy(ProductCategory $category)
    {
        $category->delete();
        return back()->with('success', 'Category deleted.');
    }
}
