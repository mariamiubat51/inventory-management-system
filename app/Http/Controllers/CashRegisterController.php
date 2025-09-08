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
        $todayRegister = CashRegister::where('date', now()->toDateString())->first();
        if ($todayRegister) {
            $todayRegister->total_sales = Sale::whereDate('created_at', $todayRegister->date)->sum('grand_total');
        }

        // Open register should only be today
        $openRegister = CashRegister::where('date', now()->toDateString())
                                    ->where('status', 'open')
                                    ->first();

        return view('cashregister.index', compact('cashRegisters', 'todayRegister', 'openRegister'));
    }

    // Open Register
    public function openRegister(Request $request)
    {
        $request->validate([
            'opening_amount' => 'required|numeric|min:0',
        ]);

        $today = now()->toDateString();

        // Check if today's register already exists
        $existing = CashRegister::where('date', $today)->first();
        if ($existing) {
            return back()->with('error', 'Register already opened for today.');
        }

        // Automatically close all previous open registers
        CashRegister::where('date', '<', $today)
            ->where('status', 'open')
            ->get()
            ->each(function($register){
                $totalSales = Sale::whereDate('created_at', $register->date)->sum('grand_total');
                $register->update([
                    'total_sales' => $totalSales,
                    'closing_amount' => $register->opening_amount + $totalSales,
                    'status' => 'closed',
                ]);
            });

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
        $register = CashRegister::where('date', $today)
            ->where('status', 'open')
            ->first();

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
