<?php

namespace App\Http\Controllers;

use App\Models\TransactionLog;
use App\Models\Account;
use Illuminate\Http\Request;

class TransactionLogController extends Controller
{
    // List all transactions with pagination and optional filtering
    public function index(Request $request)
    {
        $query = TransactionLog::with('account')->orderBy('id', 'desc');

        // Handle invalid date range first
        if ($request->filled('date_from') && $request->filled('date_to') && $request->date_to < $request->date_from) {
            // Return empty results and show error
            $transactions = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            $accounts = Account::all();
            return view('transaction_logs.index', compact('transactions', 'accounts'))
                ->withErrors(['date_to' => 'The "To Date" must be greater than or equal to the "From Date".']);
        }

        // Filter by account_id
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        // Filter by transaction_type
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        $transactions = $query->simplePaginate(15);
        $accounts = Account::all();

        return view('transaction_logs.index', compact('transactions', 'accounts'));
    }


    // Show form to create a new transaction log entry
    public function create()
    {
        $accounts = Account::all();
        return view('transaction_logs.create', compact('accounts'));
    }

    // Store new transaction log
    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_type' => 'required|string|max:255',
            'related_id' => 'nullable|integer',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:debit,credit',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:500',
        ]);

        TransactionLog::create($validated);

        return redirect()->route('transaction_logs.index')->with('success', 'Transaction created successfully.');
    }

    // (Optional) You can add show/edit/update/destroy methods as needed
}
