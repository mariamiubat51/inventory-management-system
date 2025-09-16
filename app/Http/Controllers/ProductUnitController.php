<?php

namespace App\Http\Controllers;

use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;


class ProductUnitController extends Controller
{
    public function index(Request $request)
    {
        // Start query
        $query = ProductUnit::orderBy('name');

        // Filter by status
        if ($request->filled('status')) {
            $isActive = $request->status === 'active' ? 1 : 0;
            $query->where('is_active', $isActive);
        }

        // Handle invalid date range
        if ($request->filled('from_date') && $request->filled('to_date') && $request->to_date < $request->from_date) {
            $units = new LengthAwarePaginator([], 0, 15);
            return view('products.units.index', compact('units'))
                ->withErrors(['to_date' => 'The "To Date" must be greater than or equal to the "From Date".']);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        // Paginate results
        $units = $query->simplePaginate(15)->appends($request->all());

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
