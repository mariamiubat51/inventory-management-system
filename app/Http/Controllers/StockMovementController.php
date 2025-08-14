<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Auth;

class StockMovementController extends Controller
{
    public function index()
    {
        $movements = StockMovement::with('product','user')->orderBy('created_at','desc')->get();
        return view('stock_movements.index', compact('movements'));
    }

    public function create()
    {
        $products = Product::all();
        return view('stock_movements.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'movement_type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($request->product_id);

        // Calculate new stock balance
        $new_balance = $request->movement_type == 'in' 
            ? $product->stock + $request->quantity 
            : $product->stock - $request->quantity;

        if($new_balance < 0){
            return back()->with('error', 'Stock cannot be negative.');
        }

        // Save movement
        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => $request->movement_type,
            'quantity' => $request->quantity,
            'reference' => $request->reference,
            'balance' => $new_balance,
            'user_id' => Auth::id(),
            'remarks' => $request->remarks,
        ]);

        // Update product stock
        $product->update(['stock' => $new_balance]);

        return redirect()->route('stock_movements.index')->with('success','Stock movement recorded.');
    }
}
