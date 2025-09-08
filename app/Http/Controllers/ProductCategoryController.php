<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::orderBy('name')->get();
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
