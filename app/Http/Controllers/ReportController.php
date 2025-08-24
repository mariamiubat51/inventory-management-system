<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\User;
use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index'); // main reports page
    }

    public function profit()
    {
        // Example: calculate profit = total sales - total purchases
        $totalSales = Sale::sum('grand_total');
        $totalPurchases = Purchase::sum('total_amount');
        $profit = $totalSales - $totalPurchases;

        return view('reports.profit', compact('totalSales', 'totalPurchases', 'profit'));
    }

    public function lowstock()
    {
        $products = Product::whereColumn('stock_qty', '<', 'reorder_level')->get();
        return view('reports.lowstock', compact('products'));
    }

    public function sales(Request $request)
    {
        // 1️⃣ Default date filter to include all sales
        $fromDate = $request->input('from_date', '2000-01-01');
        $toDate = $request->input('to_date', now()->toDateString());

        // 2️⃣ Start query
        $query = Sale::with('customer');

        // 3️⃣ Apply filters
        $query->whereBetween('sale_date', [$fromDate, $toDate]);

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // 4️⃣ Prepare chart data
        $chartData = (clone $query)
            ->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(grand_total) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 5️⃣ Get table data
        $sales = $query->orderBy('sale_date', 'desc')->get();

        // 6️⃣ Calculate totals
        $totalSales = $sales->sum('grand_total');
        $totalPaid  = $sales->sum('paid_amount');
        $totalDue   = $sales->sum('due_amount');

        // 7️⃣ Get customers for dropdown
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

    public function stock(Request $request)
    {
        $products = Product::with('category')->get(); // Get all products with category info
        return view('reports.stock', compact('StockMovement'));
    }

    public function expenses()
    {
        $expenses = Expense::latest()->get();
        return view('reports.expenses', compact('expenses'));
    }
}
