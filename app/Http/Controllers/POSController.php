<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use App\Models\Customer;
use App\Models\Account;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\TransactionLog;
use App\Models\StockMovement;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException; // <-- Add this

class POSController extends Controller
{
    public function index()
    {
        $products = Product::all();
        $customers = User::where('role', 'customer')->get();
        $accounts = Account::all();

        return view('pos.index', compact('products', 'customers', 'accounts'));
    }


    public function store(Request $request)
{
    // --- FIX: Updated validation rules for new discount fields ---
    $request->validate([
        'customer_id' => 'required|string',
        'items' => 'required|array|min:1',
        'items.*.id' => 'required|exists:products,id',
        'items.*.qty' => 'required|integer|min:1',
        'items.*.price' => 'required|numeric|min:0',
        'paid_amount' => 'required|numeric|min:0',
        'payment_method' => 'required|string',
        'account_id' => 'required|exists:accounts,id',
        'walkin_name' => 'required_if:customer_id,walkin|string|max:255',
        'walkin_email' => 'nullable|email|max:255',
        'discount_value' => 'nullable|numeric|min:0',
        'discount_type' => 'required_with:discount_value|string|in:fixed,percent',
    ]);

    if ($request->customer_id === 'walkin' && $request->walkin_email) {
        if (User::where('email', $request->walkin_email)->exists()) {
            throw ValidationException::withMessages([
                'walkin_email' => 'This email is already registered.'
            ]);
        }
    }

    DB::beginTransaction();

    try {
        if ($request->customer_id === 'walkin') {
            $customer = User::create([
                'name' => $request->walkin_name, 'email' => $request->walkin_email,
                'phone' => $request->walkin_phone, 'address' => $request->walkin_address,
                'password' => bcrypt(Str::random(10)), 'role' => 'customer', 'type' => 'Walk-in',
            ]);

            Customer::create([
                'name' => $request->walkin_name,
                'email' => $request->walkin_email,
                'phone' => $request->walkin_phone,
                'address' => $request->walkin_address,
                'type' => 'Walk-in',
            ]);

            $customerId = $customer->id;
        } else {
            $customerId = $request->customer_id;
        }

        $subtotal = 0;
        foreach ($request->items as $item) {
            $subtotal += $item['qty'] * $item['price'];
        }

        // --- FIX: Securely recalculate discount on the server ---
        $discountValue = $request->discount_value ?? 0;
        $discountType = $request->discount_type;
        $discountAmount = 0;

        if ($discountType === 'percent') {
            $discountAmount = ($subtotal * $discountValue) / 100;
        } else {
            $discountAmount = $discountValue;
        }

        $grandTotal = $subtotal - $discountAmount;
        $paidAmount = $request->paid_amount;
        $dueAmount = $grandTotal - $paidAmount;
        $invoiceNo = 'INV-' . strtoupper(Str::random(6));

        $sale = Sale::create([
            'invoice_no' => $invoiceNo, 'sale_date' => now(),
            'customer_id' => $customerId, 'subtotal' => $subtotal,
            'discount' => $discountAmount, // Save the calculated amount
            'grand_total' => $grandTotal, 'paid_amount' => $paidAmount,
            'due_amount' => $dueAmount, 'payment_method' => $request->payment_method,
            'note' => $request->note ?? null,
        ]);

        foreach ($request->items as $item) {
            $product = Product::find($item['id']);
            if ($product->stock_quantity < $item['qty']) {
                throw new \Exception("Insufficient stock for product: {$product->name}.");
            }
            $product->decrement('stock_quantity', $item['qty']); // Use atomic update

            $currentStock = $product->stock_quantity; // New stock after decrement

            StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'out', // because stock is sold
                'quantity' => $item['qty'],
                'balance' => $currentStock,
                'user_id' => auth()->id(),
                'reference' => 'Sale Invoice: ' . $sale->invoice_no,
                'remarks' => 'Stock reduced after POS sale',
            ]);

            SaleItem::create([
                'sale_id' => $sale->id, 'product_id' => $item['id'],
                'quantity' => $item['qty'], 'selling_price' => $item['price'],
                'total' => $item['qty'] * $item['price'],
            ]);
        }
        
        if ($paidAmount > 0) {
            $account = Account::find($request->account_id);
            if ($account) {
                $account->increment('total_balance', $paidAmount); // Use atomic update
                TransactionLog::create([
                    'transaction_date' => now(), 'transaction_type' => 'Income',
                    'account_id' => $account->id, 'amount' => $paidAmount,
                    'description' => 'POS Sale Invoice: ' . $sale->invoice_no,
                    'related_model' => 'Sale', 'related_model_id' => $sale->id,
                ]);
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Sale completed successfully!'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 422);
    }
}
}