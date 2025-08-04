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
        $request->validate([
            'date' => 'required|date',
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0',
            'account_id' => 'nullable|exists:accounts,id',
        ]);

        $expense = Expense::create($request->all());

        // Optional: log transaction
        if ($request->account_id) {
            TransactionLog::create([
                'account_id' => $request->account_id,
                'type' => 'debit',
                'transaction_type' => 'Expense',
                'amount' => $request->amount,
                'description' => $request->title,
                'transaction_date' => $request->date,
            ]);
        }

        return redirect()->route('expenses.index')->with('success', 'Expense added successfully.');
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

        $expense->update($request->all());

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    // Delete expense
    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }
}

