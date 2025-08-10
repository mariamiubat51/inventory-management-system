<?php


namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Account;
use App\Models\TransactionLog;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    // Show all expenses
    public function index()
    {
        $expenses = Expense::with('category', 'account')->latest()->get();
        return view('expenses.index', compact('expenses'));
    }

    // Show form to add expense
    public function create()
    {
        $categories = ExpenseCategory::all();
        $accounts = Account::all();
        $cashAccountId = Account::where('account_name', 'Cash')->value('id'); // get Cash account ID
        return view('expenses.create', compact('categories', 'accounts', 'cashAccountId'));
    }

    // Store new expense
    public function store(Request $request)
    {
        // Step 1: Validate inputs
        $request->validate([
            'date' => 'required|date',
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'account_id' => 'nullable|exists:accounts,id',
        ]);

        // Step 2: Start database transaction for safety
        \DB::beginTransaction();

        try {
            $accountId = $request->account_id;
            $amount = $request->amount;

            // Step 3: If account is selected, check balance
            if ($accountId) {
                $account = Account::find($accountId);

                if (!$account) {
                    return back()->withErrors(['account_id' => 'Selected account does not exist.'])->withInput();
                }


                if ($account->total_balance < $amount) {
                    return back()->withErrors(['account_id' => 'Insufficient balance in the selected account.'])->withInput();
                }

                $account->total_balance -= $amount;
                $account->save();

            }

            // Step 5: Save expense record
            $expense = Expense::create($request->all());

            // Optional: Log transaction for audit
            if ($accountId) {
                TransactionLog::create([
                    'account_id' => $accountId,
                    'type' => 'debit',
                    'transaction_type' => 'Expense',
                    'amount' => $amount,
                    'description' => $request->title,
                    'transaction_date' => $request->date,
                ]);
            }

            // Step 6: Commit transaction (make changes permanent)
            \DB::commit();

            // Step 7: Redirect success
            return redirect()->route('expenses.index')->with('success', 'Expense added successfully.');
        } catch (\Exception $e) {
            // Step 8: Rollback if error occurs
            \DB::rollBack();

            // Show error message
            return back()->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()])->withInput();
        }
    }

    // Show edit form
    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::all();
        $accounts = Account::all();
        return view('expenses.edit', compact('expense', 'categories', 'accounts'));
    }

    // Update expense
    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'date' => 'required|date',
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0',
            'account_id' => 'nullable|exists:accounts,id',
        ]);

        \DB::beginTransaction();

        try {
            // Refund old expense amount to old account (if any)
            if ($expense->account_id) {
                $oldAccount = Account::find($expense->account_id);
                if ($oldAccount) {
                    $oldAccount->total_balance += $expense->amount;
                    $oldAccount->save();
                }
            }

            $newAccountId = $request->account_id;
            $newAmount = $request->amount;

            // Deduct new amount from new account (if selected)
            if ($newAccountId) {
                $newAccount = Account::find($newAccountId);

                if (!$newAccount) {
                    return back()->withErrors(['account_id' => 'Selected account does not exist.'])->withInput();
                }

                if ($newAccount->total_balance < $newAmount) {
                    return back()->withErrors(['account_id' => 'Insufficient balance in the selected account.'])->withInput();
                }

                $newAccount->total_balance -= $newAmount;
                $newAccount->save();
            }

            // Update expense
            $expense->update($request->all());

            // Delete old transaction logs for this expense
            TransactionLog::where('transaction_type', 'Expense')
                ->where('related_id', $expense->id)
                ->delete();

            // Create new transaction log if account selected
            if ($newAccountId) {
                TransactionLog::create([
                    'account_id' => $newAccountId,
                    'type' => 'debit',
                    'transaction_type' => 'Expense',
                    'amount' => $newAmount,
                    'description' => $request->title,
                    'transaction_date' => $request->date,
                    'related_id' => $expense->id, // so we can track this expense log
                ]);
            }

            \DB::commit();

            return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');

        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()])->withInput();
        }
    }

    // Delete expense
    public function destroy(Expense $expense)
    {
        \DB::beginTransaction();

        try {
            // Refund amount to account if exists
            if ($expense->account_id) {
                $account = Account::find($expense->account_id);
                if ($account) {
                    $account->total_balance += $expense->amount;
                    $account->save();
                }
            }

            // Delete related transaction logs
            TransactionLog::where('transaction_type', 'Expense')
                ->where('related_id', $expense->id)
                ->delete();

            $expense->delete();

            \DB::commit();

            return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }

}

