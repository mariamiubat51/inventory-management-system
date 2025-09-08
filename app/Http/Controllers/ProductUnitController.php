<?php

namespace App\Http\Controllers;

use App\Models\ProductUnit;
use Illuminate\Http\Request;

class ProductUnitController extends Controller
{
    public function index()
    {
        $units = ProductUnit::orderBy('name')->get();
        return view('products.units.index', compact('units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:100|unique:product_units,name',
            'symbol' => 'nullable|string|max:16',
        ]);

        ProductUnit::create([
            'name'   => $validated['name'],
            'symbol' => $validated['symbol'] ?? null,
            'is_active' => $request->boolean('is_active')? true : false,
        ]);

        return back()->with('success', 'Unit added successfully.');
    }

    public function update(Request $request, ProductUnit $unit)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:100|unique:product_units,name,' . $unit->id,
            'symbol' => 'nullable|string|max:16',
        ]);

        $unit->update([
            'name'   => $validated['name'],
            'symbol' => $validated['symbol'] ?? null,
            'is_active' => $request->boolean('is_active')? true : false,
        ]);

        return back()->with('success', 'Unit updated successfully.');
    }

    public function destroy(ProductUnit $unit)
    {
        $unit->delete();
        return back()->with('success', 'Unit deleted.');
    }
}
