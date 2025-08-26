<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\TransactionLog;
use Carbon\Carbon;
use DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Date filter (default: current month)
        $from_date = $request->from_date ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $to_date   = $request->to_date ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        // 🔹 Key Metrics
        $totalSales = Sale::whereBetween('sale_date', [$from_date, $to_date])->sum('grand_total');

        // purchases table uses total_amount ✅
        $totalPurchases = Purchase::whereBetween('purchase_date', [$from_date, $to_date])->sum('total_amount');

        $totalProfit = $totalSales - $totalPurchases;

        $lowStockCount = Product::whereColumn('stock_quantity', '<=', 'reorder_level')->count();

        // 🔹 Charts Data
        $salesTrend = Sale::select(DB::raw('DATE(sale_date) as date'), DB::raw('SUM(grand_total) as total'))
            ->whereBetween('sale_date', [$from_date, $to_date])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $purchaseTrend = Purchase::select(DB::raw('DATE(purchase_date) as date'), DB::raw('SUM(total_amount) as total'))
            ->whereBetween('purchase_date', [$from_date, $to_date])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topProducts = Sale::join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->select('products.name', DB::raw('SUM(sale_items.quantity) as total_sold'))
            ->groupBy('products.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'reorder_level')
            ->select('name', 'stock_quantity')
            ->get();

        // 🔹 Recent Activity
        $recentSales = Sale::latest()->take(5)->get();
        $recentPurchases = Purchase::latest()->take(5)->get();
        $recentTransactions = TransactionLog::latest()->take(5)->get();

        return view('dashboard.index', compact(
            'from_date', 'to_date',
            'totalSales', 'totalPurchases', 'totalProfit', 'lowStockCount',
            'salesTrend', 'purchaseTrend', 'topProducts', 'lowStockProducts',
            'recentSales', 'recentPurchases', 'recentTransactions'
        ));
    }
}
