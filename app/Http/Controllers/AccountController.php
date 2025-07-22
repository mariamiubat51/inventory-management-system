<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $query = Account::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('account_name', 'like', "%$search%")
                ->orWhere('account_type', 'like', "%$search%");
        }

        $accounts = $query->orderBy('id', 'desc')->get();

        return view('accounts.index', compact('accounts'));
    }


    public function create()
    {
        $types = ['cash', 'bank', 'others']; // Match ENUM in database
        return view('accounts.create', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:cash,bank,others', // Match ENUM
            'initial_balance' => 'required|numeric|min:0',
        ]);

        // Generate next account code like ACC-001, ACC-002, ...
        $lastAccount = Account::orderBy('id', 'desc')->first();
        $lastCode = $lastAccount ? $lastAccount->account_code : 'ACC-000';
        $nextNumber = intval(substr($lastCode, 4)) + 1;
        $newCode = 'ACC-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        Account::create([
            'account_code' => $newCode,
            'account_name' => $request->account_name,
            'account_type' => $request->account_type,
            'initial_balance' => $request->initial_balance,
            'total_balance' => $request->initial_balance, // Corrected field name
            'note' => $request->note,
        ]);

        return redirect()->route('accounts.index')->with('success', 'Account created successfully.');
    }

    public function edit($id)
    {
        $account = Account::findOrFail($id);
        $types = ['cash', 'bank', 'others']; // Match ENUM
        return view('accounts.edit', compact('account', 'types'));
    }

    public function update(Request $request, $id)
    {
        $account = Account::findOrFail($id);

        $request->validate([
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:cash,bank,others', // Match ENUM
            'initial_balance' => 'required|numeric|min:0',
        ]);

        $account->update([
            'account_name' => $request->account_name,
            'account_type' => $request->account_type,
            'initial_balance' => $request->initial_balance,
            // Optionally: also update total_balance if needed
            'note' => $request->note,
        ]);

        return redirect()->route('accounts.index')->with('success', 'Account updated successfully.');
    }

    public function destroy($id)
    {
        $account = Account::findOrFail($id);

        // Optional: check if the account is used in transactions

        $account->delete();

        return redirect()->route('accounts.index')->with('success', 'Account deleted successfully.');
    }

    public function ledger($id)
    {
        $account = Account::findOrFail($id);

        // TODO: Fetch ledger transactions for the account.
        // For now, just a dummy empty array
        $entries = [];

        return view('accounts.ledger', compact('account', 'entries'));
    }


    /*    public function ledger($id)
    {
        $account = Account::findOrFail($id);

        // Example: get transactions for this account ordered by date
        $entries = \DB::table('transactions')
            ->where('account_id', $id)
            ->orderBy('date', 'desc')
            ->get();

        return view('accounts.ledger', compact('account', 'entries'));
    }*/

}
