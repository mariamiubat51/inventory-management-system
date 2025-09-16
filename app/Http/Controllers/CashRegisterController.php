<?php

namespace App\Http\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use App\Models\CashRegister;
use App\Models\Sale;

class CashRegisterController extends Controller
{
    public function index(Request $request)
{
    // Base query
    $query = CashRegister::orderBy('date', 'desc');

    // Handle invalid date range
    if ($request->filled('from_date') && $request->filled('to_date') && $request->to_date < $request->from_date) {
        $cashRegisters = collect(); // empty collection
        $todayRegister = null;
        $openRegister = null;

        return view('cashregister.index', compact('cashRegisters', 'todayRegister', 'openRegister'))
            ->withErrors(['to_date' => 'The "To Date" must be greater than or equal to the "From Date".']);
    }

    // Apply date filters
    if ($request->filled('from_date')) {
        $query->whereDate('date', '>=', $request->from_date);
    }
    if ($request->filled('to_date')) {
        $query->whereDate('date', '<=', $request->to_date);
    }

    // Get filtered registers
    $cashRegisters = $query->get();

    // Calculate total sales for each register
    foreach ($cashRegisters as $register) {
        $register->total_sales = Sale::whereDate('created_at', $register->date)->sum('grand_total');
    }

    // Today's register
    $todayRegister = CashRegister::whereDate('date', now()->toDateString())->first();
    if ($todayRegister) {
        $todayRegister->total_sales = Sale::whereDate('created_at', $todayRegister->date)->sum('grand_total');
    }

    // Last open register
    $openRegister = CashRegister::where('status', 'open')->orderBy('date', 'desc')->first();

    return view('cashregister.index', compact('cashRegisters', 'todayRegister', 'openRegister'));
}


    // public function index()
    // {
    //     // Get all registers, order by date descending
    //     $cashRegisters = CashRegister::orderBy('date', 'desc')->get();

    //     foreach ($cashRegisters as $register) {
    //         // Calculate total sales for display only
    //         $register->total_sales = Sale::whereDate('created_at', $register->date)->sum('grand_total');
    //     }

    //     // Get today's register if it exists
    //     $todayRegister = CashRegister::whereDate('date', now()->toDateString())->first();
    //     if ($todayRegister) {
    //         $todayRegister->total_sales = Sale::whereDate('created_at', $todayRegister->date)->sum('grand_total');
    //     }

    //     // Get the last open register (even if not today)
    //     $openRegister = CashRegister::where('status', 'open')->orderBy('date', 'desc')->first();

    //     return view('cashregister.index', compact('cashRegisters', 'todayRegister', 'openRegister'));
    // }

    // Open Register
    public function openRegister(Request $request)
    {
        $request->validate([
            'opening_amount' => 'required|numeric|min:0',
        ]);

        $today = now()->toDateString();

        // Check if any register is still open
        $existing = CashRegister::where('status', 'open')->first();
        if ($existing) {
            return back()->with('error', 'You must close the previous register before opening a new one.');
        }

        // Open today's register
        CashRegister::create([
            'date' => $today,
            'opening_amount' => $request->opening_amount,
        ]);

        return back()->with('success', 'Register opened successfully.');
    }

    // Close Register
    public function closeRegister(Request $request)
    {
        $today = now()->toDateString();
        $register = CashRegister::where('status', 'open')->latest()->first();

        if (!$register) {
            return back()->with('error', 'No open register found.');
        }

        // Calculate total sales for today
        $totalSales = Sale::whereDate('created_at', $register->date)->sum('grand_total');

        // Calculate closing amount automatically
        $closingAmount = $register->opening_amount + $totalSales;
        

        $register->update([
            'closing_amount' => $closingAmount,
            'total_sales' => $totalSales,
            'status' => 'closed',  // important
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Register closed successfully!');
    }
}
