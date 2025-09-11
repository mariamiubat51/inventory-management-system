<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashRegister;
use App\Models\Sale;

class CashRegisterController extends Controller
{
    public function index()
    {
        // Get all registers, order by date descending
        $cashRegisters = CashRegister::orderBy('date', 'desc')->get();

        foreach ($cashRegisters as $register) {
            // Calculate total sales for display only
            $register->total_sales = Sale::whereDate('created_at', $register->date)->sum('grand_total');
        }

        // Get today's register if it exists
        $todayRegister = CashRegister::whereDate('date', now()->toDateString())->first();
        if ($todayRegister) {
            $todayRegister->total_sales = Sale::whereDate('created_at', $todayRegister->date)->sum('grand_total');
        }

        // Get the last open register (even if not today)
        $openRegister = CashRegister::where('status', 'open')->orderBy('date', 'desc')->first();

        return view('cashregister.index', compact('cashRegisters', 'todayRegister', 'openRegister'));
    }

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
