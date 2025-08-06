<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    public function index()
    {
        $products = Product::all();
        $customers = User::where('role', 'customer')->get();

        return view('pos.index', compact('products', 'customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',  
        ]);

        DB::beginTransaction();

        try {
            $subtotal = collect($request->items)->sum(fn($item) => $item['price'] * $item['qty']);
            $discount = $request->discount ?? 0;  // get discount from request
            $grandTotal = $subtotal - $discount;
            $paidAmount = $request->paid ?? $grandTotal;
            $dueAmount = $grandTotal - $paidAmount;

            //  Create Sale
            $sale = Sale::create([
                'invoice_no' => 'INV-' . Str::upper(Str::random(6)),
                'customer_id' => $request->customer_id,
                'sale_date' => now(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'payment_method' => 'Cash',
                'note' => $request->note ?? null,
            ]);

            //  Create Sale Items & update stock
            foreach ($request->items as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['qty'],
                    'selling_price' => $item['price'],
                    'total' => $item['qty'] * $item['price'],
                ]);

                $product = Product::find($item['id']);
                $product->stock_quantity -= $item['qty'];
                $product->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false, // ✅ Add this
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
