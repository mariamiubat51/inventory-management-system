<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Auth;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        // Start the query to get stock movements
        $query = StockMovement::with('product', 'user')->orderBy('created_at', 'desc');

        // Apply search filter if movement_type is selected
        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        // Handle invalid date range
        if ($request->filled('from_date') && $request->filled('to_date') && $request->to_date < $request->from_date) {
            $movements = new LengthAwarePaginator([], 0, 15);
            return view('stock_movements.index', compact('movements'))
                ->withErrors(['to_date' => 'The "To Date" must be greater than or equal to the "From Date".']);
        }

        // Apply "from_date" filter if provided
        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }

        // Apply "to_date" filter if provided
        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        // Fetch the stock movements with pagination
        $movements = $query->simplePaginate(15);

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
