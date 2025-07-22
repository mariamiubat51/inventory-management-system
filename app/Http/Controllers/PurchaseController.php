<?php


namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;


class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with('supplier')->latest()->get();
        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $products = Product::all();

        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'product_id.*' => 'required|exists:products,id',
            'quantity.*' => 'required|integer|min:1',
            'buying_price.*' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $totalAmount = 0;
            $totalItems = count($request->product_id);
            $invoice = 'PUR-' . strtoupper(Str::random(6));

            // Calculate totals
            foreach ($request->product_id as $index => $productId) {
                $totalAmount += $request->quantity[$index] * $request->buying_price[$index];
            }

            $dueAmount = $totalAmount - $request->paid_amount;
            $status = $dueAmount <= 0 ? 'fully_paid' : ($request->paid_amount > 0 ? 'partially_paid' : 'unpaid');

            // Create purchase
            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'invoice_no' => $invoice,
                'purchase_date' => $request->purchase_date,
                'total_amount' => $totalAmount,
                'paid_amount' => $request->paid_amount,
                'due_amount' => $dueAmount,
                'item_count' => $totalItems,
                'payment_status' => $status,
                'purchase_status' => 'completed',
                'notes' => $request->notes,
            ]);

            // Create purchase items
            foreach ($request->product_id as $index => $productId) {
                $quantity = $request->quantity[$index];
                $buyingPrice = $request->buying_price[$index];
                $subtotal = $quantity * $buyingPrice;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'buying_price' => $buyingPrice,
                    'subtotal' => $subtotal,
                ]);

                // Update product stock
                $product = Product::find($productId);
                $product->stock_quantity += $quantity;
                $product->buying_price = $buyingPrice; // optional
                $product->save();
            }

            DB::commit();
            return redirect()->route('purchases.index')->with('success', 'Purchase added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $purchase = Purchase::with('items.product')->findOrFail($id);
        $suppliers = Supplier::all();
        $products = Product::all();

        return view('purchases.edit', compact('purchase', 'suppliers', 'products'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'product_id.*' => 'required|exists:products,id',
            'quantity.*' => 'required|integer|min:1',
            'buying_price.*' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $purchase = Purchase::findOrFail($id);

            // First, revert stock for old purchase items
            foreach ($purchase->items as $item) {
                $product = Product::find($item->product_id);
                $product->stock_quantity -= $item->quantity;
                $product->save();
            }

            $totalAmount = 0;

            // Calculate new total amount
            foreach ($request->product_id as $index => $productId) {
                $totalAmount += $request->quantity[$index] * $request->buying_price[$index];
            }

            $dueAmount = $totalAmount - $request->paid_amount;
            $paymentStatus = $dueAmount <= 0 ? 'fully_paid' : ($request->paid_amount > 0 ? 'partially_paid' : 'unpaid');

            // Update purchase main data
            $purchase->update([
                'supplier_id' => $request->supplier_id,
                'purchase_date' => $request->purchase_date,
                'total_amount' => $totalAmount,
                'paid_amount' => $request->paid_amount,
                'due_amount' => $dueAmount,
                'payment_status' => $paymentStatus,
                'purchase_status' => 'completed',
                'notes' => $request->notes,
            ]);

            // Delete old purchase items
            $purchase->items()->delete();

            // Create new purchase items and update stock
            foreach ($request->product_id as $index => $productId) {
                $quantity = $request->quantity[$index];
                $buyingPrice = $request->buying_price[$index];
                $subtotal = $quantity * $buyingPrice;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'buying_price' => $buyingPrice,
                    'subtotal' => $subtotal,
                ]);

                $product = Product::find($productId);
                $product->stock_quantity += $quantity;
                $product->buying_price = $buyingPrice; // optional
                $product->save();
            }

            DB::commit();

            return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $purchase = Purchase::with('supplier', 'items.product')->findOrFail($id);
        return view('purchases.show', compact('purchase'));
    }


}
