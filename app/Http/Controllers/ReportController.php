<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\User;
use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index'); // main reports page
    }

    public function profit(Request $request)
    {
        // 1. Date range filter
        $from_date = $request->from_date ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $to_date   = $request->to_date ?? Carbon::now()->format('Y-m-d');

        // 2. Fetch sales
        $sales = Sale::with('items.product')
            ->whereBetween('sale_date', [$from_date, $to_date])
            ->get();

        // 3. Fetch expenses
        $expenses = Expense::whereBetween('date', [$from_date, $to_date])->get();

        // 4. Calculate totals
        $totalSales = $sales->sum('grand_total');

        $totalCOGS = $sales->sum(function($sale){
            return $sale->items ? $sale->items->sum(fn($item) => $item->quantity * ($item->product->buying_price ?? 0)) : 0;
        });

        $grossProfit = $totalSales - $totalCOGS;

        $totalExpenses = $expenses->sum('amount');

        $netProfit = $grossProfit - $totalExpenses;


        // 5. Prepare data for chart (profit per day)
        $chartData = $sales->groupBy(function($sale) {
            return Carbon::parse($sale->sale_date)->format('Y-m-d'); // group by date only
        })->map(function($dailySales) {
            $dailyTotal = $dailySales->sum('grand_total');
            $dailyCOGS = $dailySales->sum(function($sale) {
                return $sale->items ? $sale->items->sum(fn($item) => $item->quantity * ($item->product->buying_price ?? 0)) : 0;
            });
            return $dailyTotal - $dailyCOGS;
        });

        $chartLabels = $chartData->keys();   // just dates
        $chartProfit = $chartData->values(); // profit values


        // 6. Return view with all data
        return view('reports.profit', compact(
            'from_date','to_date','sales','expenses',
            'totalSales','totalCOGS','grossProfit','totalExpenses','netProfit',
            'chartLabels','chartProfit'
        ));
    }

    public function sales(Request $request)
    {
        // 1 Default date filter to include all sales
        $fromDate = $request->input('from_date', now()->toDateString());
        $toDate   = $request->input('to_date', now()->toDateString());

        // 2 Start query
        $query = Sale::with('customer');

        // 3 Apply filters
        $query->whereBetween('sale_date', [$fromDate, $toDate]);

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // 4 Prepare chart data
        $chartData = (clone $query)
            ->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(grand_total) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 5 Get table data
        $sales = $query->orderBy('sale_date', 'desc')->get();

        // 6 Calculate totals
        $totalSales = $sales->sum('grand_total');
        $totalPaid  = $sales->sum('paid_amount');
        $totalDue   = $sales->sum('due_amount');

        // 7 Get customers for dropdown
        $customers = User::orderBy('name')->get(); // Use Customer::orderBy('name')->get() if separate table

        return view('reports.sales', [
            'sales'       => $sales,
            'customers'   => $customers,
            'totalSales'  => $totalSales,
            'totalPaid'   => $totalPaid,
            'totalDue'    => $totalDue,
            'chartLabels' => $chartData->pluck('date'),
            'chartValues' => $chartData->pluck('total'),
        ]);
    }

    public function purchases(Request $request)
    {
        $from_date   = $request->from_date ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $to_date     = $request->to_date ?? Carbon::now()->format('Y-m-d');
        $supplier_id = $request->supplier_id;

        $query = Purchase::with('supplier')
            ->whereBetween('purchase_date', [$from_date, $to_date]);

        if ($supplier_id) {
            $query->where('supplier_id', $supplier_id);
        }

        $purchases = $query->orderBy('purchase_date', 'desc')->get();

        $totalPurchases = $purchases->sum('total_amount');
        $totalPaid      = $purchases->sum('paid_amount');
        $totalDue       = $purchases->sum('due_amount');

        // Chart data
        $chartLabels = [];
        $chartValues = [];
        $grouped = $purchases->groupBy(function ($purchase) {
            return Carbon::parse($purchase->purchase_date)->format('d M');
        });
        foreach ($grouped as $date => $items) {
            $chartLabels[] = $date;
            $chartValues[] = $items->sum('total_amount');
        }

        $suppliers = Supplier::all();

        return view('reports.purchases', compact(
            'purchases',
            'totalPurchases',
            'totalPaid',
            'totalDue',
            'suppliers',
            'chartLabels',
            'chartValues'
        ));
    }

    public function inventory(Request $request)
    {
        // 1. PREPARE DATA FOR FILTERS
        // Get all products for the filter dropdown
        $products = Product::orderBy('name')->get();

        // 2. APPLY FILTERS TO A BASE PRODUCT QUERY
        // This query will be the source for our summary cards and chart
        $filteredProductsQuery = Product::query();
        if ($request->filled('product_id')) {
            $filteredProductsQuery->where('id', $request->product_id);
        }
        
        // Execute the query to get the collection of filtered products
        $filteredProducts = $filteredProductsQuery->get();

        // 3. CALCULATE SUMMARY CARDS FROM THE *FILTERED* DATA
        $totalProducts   = $filteredProducts->count();
        $totalInStock    = $filteredProducts->sum('stock_quantity');
        $totalOutOfStock = $filteredProducts->where('stock_quantity', '<=', 0)->count();

        // 4. Low Stock Products (where stock_quantity <= reorder_level)
        $lowStockProducts = $filteredProducts->filter(function ($product) {
            return $product->stock_quantity <= $product->reorder_level;
        });
        $lowStockCount = $lowStockProducts->count();

        // 5. PREPARE CHART DATA FROM THE *FILTERED* DATA
        $chartLabels = $filteredProducts->pluck('name');
        $chartData   = $filteredProducts->pluck('stock_quantity');

        // 6. GET STOCK MOVEMENTS BASED ON ALL FILTERS
        $movementsQuery = StockMovement::with('product')->orderBy('created_at', 'desc');

        // Apply product filter using the IDs from our filtered product list
        $filteredProductIds = $filteredProducts->pluck('id');
        $movementsQuery->whereIn('product_id', $filteredProductIds);

        // Default date range
        $from_date = $request->from_date ?? '2025-07-01'; // Replace with actual business start date
        $to_date   = $request->to_date   ?? Carbon::now()->format('Y-m-d'); // Today

        // Step 2: Apply the date filter
        $movementsQuery->whereBetween('created_at', [$from_date, $to_date]);
        
        $movements = $movementsQuery->get();

        // 7. PASS ALL DYNAMIC DATA TO THE VIEW
        return view('reports.inventory', [
            'products'        => $products, // For the dropdown
            'movements'       => $movements,
            'totalProducts'   => $totalProducts,
            'totalInStock'    => $totalInStock,
            'totalOutOfStock' => $totalOutOfStock,
            'chartLabels'     => $chartLabels,
            'chartData'       => $chartData,
            'lowStockCount'   => $lowStockCount,
            'from_date'       => $from_date,  
            'to_date'         => $to_date, 
        ]);
    }

    public function lowStock()
    {
        // Get the low stock alert from settings
        $setting = Setting::first();
        $lowStockAlert = $setting->low_stock_alert ?? 5; // default 5 if not set

        // Get products with stock_quantity <= lowStockAlert
        $lowStockProducts = Product::where('stock_quantity', '<=', $lowStockAlert)->get();

        return view('reports.low_stock', compact('lowStockProducts', 'lowStockAlert'));
    }

    public function expenses(Request $request)
    {
        // Default date range: current month
        $from_date = $request->from_date ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d');
        $to_date   = $request->to_date ?? \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d');

        // Get all expense categories for the dropdown
        $categories = \App\Models\ExpenseCategory::all();

        // Build the query with eager loading for category & account
        $query = \App\Models\Expense::with(['category', 'account'])
                    ->whereBetween('date', [$from_date, $to_date])
                    ->orderBy('date', 'desc');

        // Filter by category if selected
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Get filtered expenses
        $expenses = $query->get();

        // Total amount
        $totalExpenses = $expenses->sum('amount');

        return view('reports.expenses', compact('expenses', 'totalExpenses', 'from_date', 'to_date', 'categories'));
    }
}
