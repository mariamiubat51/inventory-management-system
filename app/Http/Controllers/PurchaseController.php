<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Account;
use App\Models\TransactionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with('supplier')->withCount('items')->latest();

        // Filter by supplier name
        if ($request->filled('supplier_name')) {
            $query->whereHas('supplier', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->supplier_name . '%');
            });
        }

        // Handle invalid date range
        if ($request->filled('from_date') && $request->filled('to_date') && $request->to_date < $request->from_date) {
            $purchases = new LengthAwarePaginator([], 0, 15);
            return view('purchases.index', compact('purchases'))
                ->withErrors(['to_date' => 'The "To Date" must be greater than or equal to the "From Date".']);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        // Paginate
        $purchases = $query->simplePaginate(15)->appends($request->all());
        $accounts = Account::all(); // required for due payment dropdown

        return view('purchases.index', compact('purchases', 'accounts'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $products = Product::all();
        $accounts = Account::all();

        return view('purchases.create', compact('suppliers', 'products', 'accounts'));
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
            'account_id' => 'required|exists:accounts,id',
        ]);

        // balance check  
        $account = Account::find($request->account_id);
        if (!$account) {
            return back()->withInput()->withErrors(['account_id' => 'Selected account not found.']);
        }
        if ($request->paid_amount > $account->total_balance) {
            return back()->withInput()->withErrors([
                'paid_amount' => "Insufficient balance in the selected account. Available: {$account->total_balance}",
            ]);
        }

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

            // Create transaction log for paid amount (payment reduces cash, so debit)
            if ($request->paid_amount > 0) {
                try {
                    $log = TransactionLog::create([
                        'transaction_type' => 'purchase',
                        'related_id' => $purchase->id,
                        'account_id' => $request->account_id,
                        'amount' => $request->paid_amount,
                        'type' => 'debit',
                        'transaction_date' => $request->purchase_date,
                        'description' => 'Payment made for purchase #' . $purchase->invoice_no,
                    ]);
                    if (!$log) {
                        \Log::error("Failed to create transaction log for purchase ID: " . $purchase->id);
                    }
                } catch (\Exception $e) {
                    \Log::error("Transaction log creation error (store): " . $e->getMessage());
                    throw $e;
                }
            }

            $account->total_balance -= $request->paid_amount;
            $account->save();


            // Create purchase items and update stock
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
                $product->buying_price = $buyingPrice;
                $product->save();

                StockMovement::create([
                    'product_id' => $product->id,
                    'movement_type' => 'in', // stock coming in
                    'quantity' => $quantity,
                    'balance' => $product->stock_quantity, // current stock after addition
                    'user_id' => auth()->id(),
                    'reference' => 'Purchase #' . $purchase->invoice_no,
                    'remarks' => 'Stock added from purchase',
                ]);
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
        $accounts = Account::all();  // Added accounts here

        return view('purchases.edit', compact('purchase', 'suppliers', 'products', 'accounts'));
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
            'account_id' => 'required|exists:accounts,id',  // Added validation here
        ]);

        // balance check 
        $account = Account::find($request->account_id);
        if (!$account) {
            return back()->withInput()->withErrors(['account_id' => 'Selected account not found.']);
        }
        if ($request->paid_amount > $account->total_balance) {
            return back()->withInput()->withErrors([
                'paid_amount' => "Insufficient balance in the selected account. Available: {$account->total_balance}",
            ]);
        }

        DB::beginTransaction();

        try {
            $purchase = Purchase::findOrFail($id);

            // Revert stock for old purchase items
            foreach ($purchase->items as $item) {
                $product = Product::find($item->product_id);
                $product->stock_quantity -= $item->quantity;
                $product->save();

                StockMovement::create([
                    'product_id' => $product->id,
                    'movement_type' => 'out', // reverting stock
                    'quantity' => $item->quantity,
                    'balance' => $product->stock_quantity,
                    'user_id' => auth()->id(),
                    'reference' => 'Purchase Update #' . $purchase->invoice_no,
                    'remarks' => 'Stock reverted during purchase update',
                ]);
            }

            $totalAmount = 0;
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

            // Remove old transaction logs for this purchase
            TransactionLog::where('transaction_type', 'purchase')
                ->where('related_id', $purchase->id)
                ->delete();

            // Create new transaction log (payment reduces cash, so debit)
            if ($request->paid_amount > 0) {
                try {
                    $log = TransactionLog::create([
                        'transaction_type' => 'purchase',
                        'related_id' => $purchase->id,
                        'account_id' => $request->account_id,
                        'amount' => $request->paid_amount,
                        'type' => 'debit',
                        'transaction_date' => $request->purchase_date,
                        'description' => 'Updated payment for purchase #' . $purchase->invoice_no,
                    ]);
                    if (!$log) {
                        \Log::error("Failed to create transaction log for purchase ID: " . $purchase->id);
                    }
                } catch (\Exception $e) {
                    \Log::error("Transaction log creation error (update): " . $e->getMessage());
                    throw $e;
                }
            }

            // Revert old payment first
            $account->total_balance += $purchase->paid_amount;

            // Subtract new payment
            $account->total_balance -= $request->paid_amount;

            $account->save();


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
                $product->buying_price = $buyingPrice;
                $product->save();

                StockMovement::create([
                    'product_id' => $product->id,
                    'movement_type' => 'in', // stock coming in
                    'quantity' => $quantity,
                    'balance' => $product->stock_quantity,
                    'user_id' => auth()->id(),
                    'reference' => 'Purchase Update #' . $purchase->invoice_no,
                    'remarks' => 'Stock updated after purchase update',
                ]);
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

    public function payDue(Request $request, $id)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'pay_amount' => 'required|numeric|min:0.01',
        ]);

        $purchase = Purchase::findOrFail($id);
        $account = Account::findOrFail($request->account_id);

        if ($request->pay_amount > $purchase->due_amount) {
            return back()->with('error', 'Payment exceeds due.');
        }

        if ($request->pay_amount > $account->total_balance) {
            return back()->with('error', 'Not enough account balance.');
        }

        // Update account balance
        $account->total_balance -= $request->pay_amount;
        $account->save();

        // Update purchase payment info
        $purchase->paid_amount += $request->pay_amount;
        $purchase->due_amount = $purchase->total_amount - $purchase->paid_amount;

        if ($purchase->due_amount == 0) {
            $purchase->payment_status = 'fully_paid';
        } elseif ($purchase->paid_amount > 0) {
            $purchase->payment_status = 'partially_paid';
        }

        $purchase->save();

        // Log transaction
        TransactionLog::create([
            'account_id' => $account->id,
            'transaction_type' => 'Debit',
            'amount' => $request->pay_amount,
            'description' => 'Due payment for Purchase #' . $purchase->invoice_no,
            'transaction_date' => now(),
        ]);

    return redirect()->route('purchases.index')->with('success', 'Due paid successfully.');
    }

}
