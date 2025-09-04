<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashRegister;
use App\Models\Sale;

class CashRegisterController extends Controller
{
    public function index()
    {
        $cashRegisters = CashRegister::orderBy('date', 'desc')->get();

        foreach ($cashRegisters as $register) {
            $register->total_sales = Sale::whereDate('created_at', $register->date)->sum('grand_total');
        }

        // today register
        $todayRegister = CashRegister::where('date', now()->toDateString())->first();

        if ($todayRegister) {
            $todayRegister->total_sales = Sale::whereDate('created_at', $todayRegister->date)->sum('grand_total');
        }

        return view('cashregister.index', compact('cashRegisters', 'todayRegister'));
    }

    // Open Register
    public function openRegister(Request $request)
    {
        $request->validate([
            'opening_amount' => 'required|numeric|min:0',
        ]);

        $today = now()->toDateString();
        $existing = CashRegister::where('date', $today)->first();

        if ($existing) {
            return back()->with('error', 'Register already opened for today.');
        }

        // Check if yesterday's register is still open
        $yesterday = now()->subDay()->toDateString();
        $openYesterday = CashRegister::where('date', $yesterday)
            ->where('status', 'open')
            ->first();
        if ($openYesterday) {
            return back()->with('error', 'Close yesterday\'s register first.');
        }

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
        $register = CashRegister::where('date', $today)
            ->where('status', 'open')
            ->first();

        if (!$register) {
            return back()->with('error', 'No open register found.');
        }

        $totalSales = Sale::whereDate('created_at', $register->date)->sum('grand_total');
        $cashInHand = $register->opening_amount + $totalSales;

        $register->update([
            'closing_amount' => $request->closing_amount ?? $cashInHand,
            'total_sales' => $totalSales,
            'status' => 'closed',
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Register closed.');
    }
}
