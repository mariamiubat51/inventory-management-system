<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    // Show all sales
    public function index()
    {
        $sales = Sale::with('customer')->latest()->paginate(10);
        return view('sales.index', compact('sales'));
    }

    // Show create sale form
    public function create()
    {
        $customers = User::where('role', 'customer')->get();
        $products = Product::select('id', 'name', 'selling_price', 'stock_quantity')->get();
        return view('sales.create', compact('customers', 'products'));
    }

    // Store a new sale
    public function store(Request $request)
    {
        $request->validate([
            'sale_date' => 'required|date',
            'customer_id' => 'nullable|exists:users,id',
            'product_id.*' => 'required|exists:products,id',
            'quantity.*' => 'required|integer|min:1',
            'selling_price.*' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $invoiceNo = 'INV-' . strtoupper(Str::random(6));
            $subtotal = 0;

            foreach ($request->quantity as $i => $qty) {
                $subtotal += $qty * $request->selling_price[$i];
            }

            $discount = $request->discount ?? 0;
            $grandTotal = $subtotal - $discount;
            $paidAmount = $request->paid_amount;
            $dueAmount = $grandTotal - $paidAmount;

            $sale = Sale::create([
                'invoice_no' => $invoiceNo,
                'sale_date' => $request->sale_date,
                'customer_id' => $request->customer_id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'payment_method' => $request->payment_method,
                'note' => $request->note,
            ]);

            foreach ($request->product_id as $i => $productId) {
                $qty = $request->quantity[$i];
                $price = $request->selling_price[$i];
                $total = $qty * $price;

                $product = Product::findOrFail($productId);
                if ($product->stock_quantity < $qty) {
                    throw new \Exception("Not enough stock for product: {$product->name}");
                }

                $product->stock_quantity -= $qty;
                $product->save();

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $productId,
                    'quantity' => $qty,
                    'selling_price' => $price,
                    'total' => $total,
                ]);
            }

            // TODO: Add transaction log if needed

            DB::commit();
            return redirect()->route('sales.index')->with('success', 'Sale created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors($e->getMessage())->withInput();
        }
    }

    // View single sale
    public function show($id)
    {
        $sale = Sale::with(['customer', 'items.product'])->findOrFail($id);
        return view('sales.show', compact('sale'));
    }

    // Show edit sale form
    public function edit($id)
    {
        $sale = Sale::with('items')->findOrFail($id);
        $customers = User::where('role', 'customer')->get();
        $products = Product::all();
        return view('sales.edit', compact('sale', 'customers', 'products'));
    }

    // Update sale
    public function update(Request $request, $id)
    {
        $request->validate([
            'sale_date' => 'required|date',
            'customer_id' => 'nullable|exists:users,id',
            'product_id.*' => 'required|exists:products,id',
            'quantity.*' => 'required|integer|min:1',
            'selling_price.*' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $sale = Sale::findOrFail($id);

            // Restore old stock
            foreach ($sale->items as $item) {
                $product = Product::findOrFail($item->product_id);
                $product->stock_quantity += $item->quantity;
                $product->save();
            }

            // Delete old items
            $sale->items()->delete();

            // Recalculate totals
            $subtotal = 0;
            foreach ($request->quantity as $i => $qty) {
                $subtotal += $qty * $request->selling_price[$i];
            }
            $discount = $request->discount ?? 0;
            $grandTotal = $subtotal - $discount;
            $paidAmount = $request->paid_amount;
            $dueAmount = $grandTotal - $paidAmount;

            // Update main sale record
            $sale->update([
                'sale_date' => $request->sale_date,
                'customer_id' => $request->customer_id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'payment_method' => $request->payment_method,
                'note' => $request->note,
            ]);

            // Add updated items
            foreach ($request->product_id as $i => $productId) {
                $qty = $request->quantity[$i];
                $price = $request->selling_price[$i];
                $total = $qty * $price;

                $product = Product::findOrFail($productId);
                if ($product->stock_quantity < $qty) {
                    throw new \Exception("Not enough stock for product: {$product->name}");
                }

                $product->stock_quantity -= $qty;
                $product->save();

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $productId,
                    'quantity' => $qty,
                    'selling_price' => $price,
                    'total' => $total,
                ]);
            }

            // TODO: Update transaction log if needed

            DB::commit();
            return redirect()->route('sales.index')->with('success', 'Sale updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors($e->getMessage())->withInput();
        }
    }

    // Delete sale
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $sale = Sale::findOrFail($id);

            // Restore stock
            foreach ($sale->items as $item) {
                $product = Product::findOrFail($item->product_id);
                $product->stock_quantity += $item->quantity;
                $product->save();
            }

            $sale->items()->delete();
            $sale->delete();

            // TODO: Delete transaction log if applicable

            DB::commit();
            return redirect()->route('sales.index')->with('success', 'Sale deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors($e->getMessage());
        }
    }
}
