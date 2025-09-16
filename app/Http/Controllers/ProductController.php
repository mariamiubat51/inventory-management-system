<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;


class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Start the query with eager loading for category and unit
        $query = Product::latest()->with(['category', 'unit']);

        // Filter by product name if provided
        if ($request->filled('product_name')) {
            $query->where('name', 'like', '%' . $request->product_name . '%');
        }

        // Handle invalid date range
        if ($request->filled('from_date') && $request->filled('to_date') && $request->to_date < $request->from_date) {
            $products = new LengthAwarePaginator([], 0, 15);
            return view('products.index', compact('products'))
                ->withErrors(['to_date' => 'The "To Date" must be greater than or equal to the "From Date".']);
        }

        // Filter by 'from_date' if provided
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        // Filter by 'to_date' if provided
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Get filtered products with pagination
        $products = $query->simplePaginate(15);

        return view('products.index', compact('products'));
    }

    public function create()
{
    $categories = ProductCategory::where('is_active', true)->get();
    $units = ProductUnit::where('is_active', true)->get(); // fetch all units
    $settings = \App\Models\Setting::first();
    $default_unit_id = $settings->default_unit_id ?? null; // get default unit from settings

    return view('products.create', compact('categories', 'units', 'default_unit_id'));
}


    public function store(Request $request)
    {
        // Validate inputs, including category_id and unit_id
        $request->validate([
            'name' => 'required',
            'description' => 'nullable|string',
            'buying_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:product_categories,id', // Validate category
            'unit_id' => 'required|exists:product_units,id', // Validate unit
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Generate unique product code
        $productCode = 'PRD' . now()->format('Ymd') . rand(1000, 9999);

        $data = $request->only([
            'name',
            'description',
            'buying_price',
            'selling_price',
            'stock_quantity',
            'category_id',  // Add category_id
            'unit_id',      // Add unit_id
        ]);

        $data['product_code'] = $productCode;
        // Auto-generate unique barcode
        $data['barcode'] = 'BC-' . strtoupper(uniqid());

        // Handle Image Upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        // Save Product
        $product = Product::create($data);

        // Add Stock Movement
        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'in', // stock coming in
            'quantity' => $product->stock_quantity,
            'balance' => $product->stock_quantity,
            'user_id' => auth()->id() ?? 1,
            'reference' => 'Product Creation',
            'remarks' => 'Product created with initial stock',
        ]);

        return redirect()->route('products.index')->with('success', 'Product created with barcode successfully.');
    }

    public function edit(Product $product)
    {
        $categories = ProductCategory::where('is_active', true)->get();
        $units = ProductUnit::all(); // <- Fetch units
        $settings = \App\Models\Setting::first();
        $default_unit_id = $product->unit_id ?? $settings->default_unit_id ?? null;

        return view('products.edit', compact('product', 'categories', 'units', 'default_unit_id'));
    }

    public function update(Request $request, Product $product)
    {
        // Validate inputs, including category_id and unit_id
        $request->validate([
            'name' => 'required',
            'description' => 'nullable|string',
            'buying_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:product_categories,id', // Validate category
            'unit_id' => 'required|exists:product_units,id', // Validate unit
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only([
            'name',
            'description',
            'buying_price',
            'selling_price',
            'stock_quantity',
            'category_id',  // Add category_id
            'unit_id',      // Add unit_id
        ]);

        // Image Upload
        if ($request->hasFile('image')) {
            // Delete the old image
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        // If stock quantity is updated, create a stock movement
        if ($product->wasChanged('stock_quantity')) {
            $oldQty = $product->getOriginal('stock_quantity');
            $newQty = $product->stock_quantity;
            $difference = $newQty - $oldQty;

            StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => $difference > 0 ? 'in' : 'out',
                'quantity' => abs($difference),
                'balance' => $newQty,
                'user_id' => auth()->id() ?? 1,
                'reference' => 'Stock Adjustment',
                'remarks' => "Stock changed from {$oldQty} to {$newQty}",
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        // Delete the image first
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
