<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->get();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'nullable|string',
            'buying_price' => 'required|numeric|min:0',            
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
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
        ]);

        $data['product_code'] = $productCode;

        //  Auto-generate unique barcode
        $data['barcode'] = 'BC-' . strtoupper(uniqid());

        //  Handle Image Upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        //  Save Product
        $product = Product::create($data);

        //  Add Stock Movement
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
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'nullable|string',
            'buying_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only([
            'name',
            'description',
            'buying_price',
            'selling_price',
            'stock_quantity',
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
