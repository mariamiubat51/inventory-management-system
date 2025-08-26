@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Dashboard</h2>
        <div class="d-flex align-items-center gap-3">
            <span class="fw-medium">{{ Auth::user()->name }}</span>
            <a href="{{ route('logout') }}" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="mb-4">
        <input type="text" class="form-control form-control-lg" placeholder="Search products, sales, purchases, customers">
    </div>

    {{-- Key Metrics --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center p-3 bg-primary text-white">
                <h6>Total Profit</h6>
                <h3 class="fw-bold">{{ number_format($totalProfit,2) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center p-3 bg-success text-white">
                <h6>Total Sales</h6>
                <h3 class="fw-bold">{{ number_format($totalSales,2) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center p-3 bg-warning text-dark">
                <h6>Total Purchases</h6>
                <h3 class="fw-bold">{{ number_format($totalPurchases,2) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center p-3 bg-danger text-white">
                <h6>Low Stock</h6>
                <h3 class="fw-bold">{{ $lowStockCount }}</h3>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm p-3 border-0">
                <h5 class="fw-bold mb-3">Sales Trend</h5>
                <canvas id="salesChart" height="200"></canvas>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm p-3 border-0">
                <h5 class="fw-bold mb-3">Top Products</h5>
                <canvas id="topProductsChart" height="200"></canvas>
            </div>
        </div>
    </div>

    {{-- Recent Activities --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm p-3 border-0">
                <h5 class="fw-bold mb-3">Recent Sales</h5>
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr><th>Invoice</th><th>Date</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @foreach($recentSales as $sale)
                        <tr>
                            <td>{{ $sale->invoice_no }}</td>
                            <td>{{ $sale->sale_date }}</td>
                            <td>{{ $sale->grand_total }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm p-3 border-0">
                <h5 class="fw-bold mb-3">Recent Purchases</h5>
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr><th>Invoice</th><th>Date</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @foreach($recentPurchases as $purchase)
                        <tr>
                            <td>{{ $purchase->invoice_no }}</td>
                            <td>{{ $purchase->purchase_date }}</td>
                            <td>{{ $purchase->grand_total }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('sales.create') }}" class="btn btn-primary btn-lg">➕ Add Sale</a>
        <a href="{{ route('purchases.create') }}" class="btn btn-success btn-lg">➕ Add Purchase</a>
        <a href="{{ route('products.create') }}" class="btn btn-warning btn-lg">➕ Add Product</a>
        <a href="{{ route('customers.create') }}" class="btn btn-info btn-lg">➕ Add Customer</a>

        <div class="dropdown">
            <button class="btn btn-dark btn-lg dropdown-toggle" type="button" id="reportsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                📑 View Reports
            </button>
            <ul class="dropdown-menu" aria-labelledby="reportsDropdown">
                <li><a class="dropdown-item" href="{{ url('/reports/profit') }}"><i class="fas fa-dollar-sign me-1"></i> Profit Report</a></li>
                <li><a class="dropdown-item" href="{{ url('/reports/inventory') }}"><i class="fas fa-warehouse me-1"></i> Inventory Report</a></li>
                <li><a class="dropdown-item" href="{{ url('/reports/sales') }}"><i class="fas fa-cart-shopping me-1"></i> Sales Report</a></li>
                <li><a class="dropdown-item" href="{{ url('/reports/purchases') }}"><i class="fas fa-shopping-cart me-1"></i> Purchase Report</a></li>
                <li><a class="dropdown-item" href="{{ url('/reports/expenses') }}"><i class="fas fa-wallet me-1"></i> Expenses Report</a></li>
            </ul>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sales Trend Chart
    const salesCtx = document.getElementById('salesChart');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($salesTrend->pluck('date')) !!},
            datasets: [{
                label: 'Sales',
                data: {!! json_encode($salesTrend->pluck('total')) !!},
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                fill: true,
                tension: 0.3
            }]
        },
        options: { responsive: true }
    });

    // Top Products Chart
    const topCtx = document.getElementById('topProductsChart');
    new Chart(topCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($topProducts->pluck('name')) !!},
            datasets: [{
                label: 'Top Products',
                data: {!! json_encode($topProducts->pluck('total_sold')) !!},
                backgroundColor: 'rgba(255, 159, 64, 0.8)',
                borderRadius: 5
            }]
        },
        options: { responsive: true }
    });
</script>
@endpush
