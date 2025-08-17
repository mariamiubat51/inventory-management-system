<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Account;
use App\Models\TransactionLog;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;


class SaleController extends Controller
{
    // Show all sales
    public function index()
    {
        $sales = Sale::with('customer')->withCount('items')->latest()->paginate(15);
        $accounts = Account::all(); 

        return view('sales.index', compact('sales', 'accounts'));
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
            'customer_id' => 'nullable',
            'product_id.*' => 'required|exists:products,id',
            'quantity.*' => 'required|integer|min:1',
            'selling_price.*' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'account_id' => 'required|exists:accounts,id',
        ]);

        if ($request->customer_id === 'walkin') {
            // Check if email already exists
            $existing = User::where('email', $request->walkin_email)->first();
            if ($existing) {
                throw ValidationException::withMessages(['walkin_email' => 'This email is already registered.']);
            }

            $customer = User::create([
                'name' => $request->walkin_name,
                'email' => $request->walkin_email,
                'phone' => $request->walkin_phone,
                'address' => $request->walkin_address,
                'password' => bcrypt('12345678'), // default password
                'role' => 'customer',
                'type' => 'Walk-in',
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
                'customer_id' => $customerId,
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

                // Reduce product stock
                $product->stock_quantity -= $qty;
                $product->save();

                // Create Stock Movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'movement_type' => 'out',
                    'quantity' => $qty,
                    'balance' => $product->stock_quantity,
                    'user_id' => auth()->id(),
                    'reference' => 'Sale #' . $sale->invoice_no,
                    'remarks' => 'Sold to customer ' . ($sale->customer ? $sale->customer->name : 'Walk-in'),
                ]);

                // Create sale item:
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $productId,
                    'quantity' => $qty,
                    'selling_price' => $price,
                    'total' => $total,
                ]);
            }

            // After the foreach loop ends — CORRECT PLACE
            if ($request->paid_amount > 0 && $request->has('account_id')) {
                $account = Account::find($request->account_id);

                if ($account) {
                    // Increase account balance only once
                    $account->total_balance += $request->paid_amount;
                    $account->save();

                    // Create transaction log entry
                    TransactionLog::create([
                        'transaction_date' => Carbon::now(),
                        'transaction_type' => 'Income',
                        'account_id' => $account->id,
                        'amount' => $request->paid_amount,
                        'description' => 'Sale Invoice: ' . $sale->invoice_no,
                        'related_model' => 'Sale',
                        'related_model_id' => $sale->id,
                    ]);
                }
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
        return view('sales.view', compact('sale'));
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

                StockMovement::create([
                    'product_id' => $product->id,
                    'movement_type' => 'in', // restoring stock
                    'quantity' => $item->quantity,
                    'balance' => $product->stock_quantity,
                    'user_id' => auth()->id(),
                    'reference' => 'Sale Update #' . $sale->invoice_no,
                    'remarks' => 'Restored stock before updating sale',
                ]);
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

                StockMovement::create([
                    'product_id' => $product->id,
                    'movement_type' => 'out',
                    'quantity' => $qty,
                    'balance' => $product->stock_quantity,
                    'user_id' => auth()->id(),
                    'reference' => 'Sale Update #' . $sale->invoice_no,
                    'remarks' => 'Updated sale for customer ' . ($sale->customer ? $sale->customer->name : 'Walk-in'),
                ]);

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
            // Handle account balance update and transaction log on update
            if ($request->paid_amount > 0 && $request->has('account_id')) {
                $account = Account::find($request->account_id);

                if ($account) {
                    // Optional: Adjust the old paid_amount (not required if replacing fully)
                    $account->total_balance += $request->paid_amount;
                    $account->save();

                    // Optional: Delete old transaction log for this sale
                    TransactionLog::where('related_model', 'Sale')
                                ->where('related_model_id', $sale->id)
                                ->delete();

                    // Create updated transaction log
                    TransactionLog::create([
                        'transaction_date' => Carbon::now(),
                        'transaction_type' => 'Income',
                        'account_id' => $account->id,
                        'amount' => $request->paid_amount,
                        'description' => 'Updated Sale Invoice: ' . $sale->invoice_no,
                        'related_model' => 'Sale',
                        'related_model_id' => $sale->id,
                    ]);
                }
            }

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

                StockMovement::create([
                    'product_id' => $product->id,
                    'movement_type' => 'in',
                    'quantity' => $item->quantity,
                    'balance' => $product->stock_quantity,
                    'user_id' => auth()->id(),
                    'reference' => 'Deleted Sale #' . $sale->invoice_no,
                    'remarks' => 'Restored stock after sale deletion',
                ]);
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

    public function payDue(Request $request, Sale $sale)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'pay_amount' => 'required|numeric|min:0.01|max:' . $sale->due_amount,
        ]);

        DB::beginTransaction();

        try {
            $account = Account::findOrFail($request->account_id);

            // Update Sale
            $sale->paid_amount += $request->pay_amount;
            $sale->due_amount -= $request->pay_amount;
            $sale->save();

            // Update Account (💰 increase balance)
            $account->total_balance += $request->pay_amount;
            $account->save();

            // Log Transaction
            TransactionLog::create([
                'transaction_date' => Carbon::now(),
                'transaction_type' => 'Income',
                'account_id' => $account->id,
                'amount' => $request->pay_amount,
                'description' => 'Due payment for Sale Invoice ' . $sale->invoice_no,
                'related_model' => 'Sale',
                'related_model_id' => $sale->id,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Due paid successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors($e->getMessage());
        }
    }

}
