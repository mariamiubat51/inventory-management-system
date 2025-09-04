<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Setting;
use App\Models\TransactionLog;
use Carbon\Carbon;
use DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        // Filter Products
        $products = Product::when($search, function($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
        })->get();

        // Filter Sales
        $recentSales = Sale::when($search, function($query, $search) {
            return $query->where('invoice_no', 'like', "%{$search}%")
                        ->orWhereHas('customer', function($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
        })->latest()->take(5)->get();

        // Filter Purchases
        $recentPurchases = Purchase::when($search, function($query, $search) {
            return $query->where('invoice_no', 'like', "%{$search}%");
        })->latest()->take(5)->get();

        // Filter Transaction Logs
        $recentTransactions = TransactionLog::when($search, function($query, $search) {
            return $query->where('description', 'like', "%{$search}%");
        })->latest()->take(5)->get();

        // Date filter (default: current month)
        $from_date = $request->from_date ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $to_date   = $request->to_date ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        // Key Metrics
        $totalSales = Sale::whereBetween('sale_date', [$from_date, $to_date])->sum('grand_total');
        $totalDue = Sale::whereBetween('sale_date', [$from_date, $to_date])
            ->sum(DB::raw('grand_total - paid_amount'));
        $totalPurchases = Purchase::whereBetween('purchase_date', [$from_date, $to_date])->sum('total_amount'); // check column name

        $totalProfit = Sale::join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->whereBetween('sales.sale_date', [$from_date, $to_date])
            ->select(DB::raw('SUM((sale_items.quantity * sale_items.selling_price) - (sale_items.quantity * products.buying_price)) as profit'))
            ->value('profit');

        $lowStockAlert = Setting::first()->low_stock_alert ?? 5;
        $lowStockCount = Product::where('stock_quantity', '<=', $lowStockAlert)->count();

        // Charts Data
        $purchasesTrend = Purchase::select(
                DB::raw('DATE(purchase_date) as date'), 
                DB::raw('SUM(total_amount) as total')
            )
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

        $mostSelling = $topProducts->first();

        $lowStockProducts = Product::where('stock_quantity', '<=', $lowStockAlert)
            ->select('name', 'stock_quantity')
            ->get();

        // Daily Revenue & Profit
        $dailyRevenue = Sale::select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(grand_total) as revenue')
            )
            ->whereBetween('sale_date', [$from_date, $to_date])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dailyProfit = Sale::join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->select(
                DB::raw('DATE(sales.sale_date) as date'),
                DB::raw('SUM(sales.grand_total - (products.buying_price * sale_items.quantity)) as profit')
            )
            ->whereBetween('sales.sale_date', [$from_date, $to_date])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Prepare arrays for chart
        $dates = [];
        $revenue = [];
        $profit = [];

        foreach ($dailyRevenue as $rev) {
            $dates[] = $rev->date;
            $revenue[] = $rev->revenue;
            $pro = $dailyProfit->firstWhere('date', $rev->date);
            $profit[] = $pro->profit ?? 0;
        }

        return view('dashboard.index', compact(
            'from_date', 'to_date',
            'totalSales', 'totalPurchases', 'totalProfit', 'lowStockCount',
            'purchasesTrend', 'topProducts', 'lowStockProducts', 'recentSales',
            'recentPurchases', 'recentTransactions', 'mostSelling', 'totalDue',
            'dates','revenue','profit', 'products', 'search'
        ));
    }

}
