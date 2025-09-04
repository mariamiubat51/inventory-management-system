@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <!-- Left: Dashboard Title -->
        <div class="d-flex align-items-center gap-3">
            <h2 class="fw-bold mb-0">Dashboard</h2>
        </div>

        <!-- Right: Search + User Info + Logout -->
        <div class="d-flex align-items-center gap-3">
            <!-- User name -->
            <span class="fw-medium">{{ Auth::user()->name }}</span>

            <!-- Logout button -->
            <form method="POST" action="{{ route('logout') }}" class="mb-0">
                @csrf
                <button class="btn btn-outline-danger btn-sm" type="submit">Logout</button>
            </form>
        </div>
    </div>


    {{-- Key Metrics --}}
    <div>
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 text-center p-3 bg-primary text-white">
                    <h6>Total Profit</h6>
                    <h3 class="fw-bold">{{ number_format($totalProfit,2) }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 text-center p-3 bg-success text-white">
                    <h6>Total Sales</h6>
                    <h3 class="fw-bold">{{ number_format($totalSales,2) }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 text-center p-3 bg-warning text-white">
                    <h6>Total Purchases</h6>
                    <h3 class="fw-bold">{{ number_format($totalPurchases,2) }}</h3>
                </div>
            </div>
        </div>
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 text-center p-3 bg-secondary text-white">
                    <h6>Total Due</h6>
                    <h3 class="fw-bold">{{ number_format($totalDue,2) }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 text-center p-3 bg-danger text-white">
                    <h6>Low Stock</h6>
                    <h3 class="fw-bold">{{ $lowStockCount }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 text-center p-3 bg-info text-white">
                    <h6>Most Selling Product</h6>
                    @if($mostSelling)
                        <h5 class="fw-bold">{{ $mostSelling->name }}</h5>
                        <p class="mb-0">Sold: {{ $mostSelling->total_sold }}</p>
                    @else
                        <p class="mb-0">No sales yet</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Charts + Tables Side by Side --}}
    <div class="row g-4 mb-4">
        {{-- Left Side - Charts --}}
        <div class="col-lg-6">
            <div class="card shadow-sm p-3 border-0 mb-4">
                <h5 class="fw-bold mb-3">Monthly Profit</h5>
                <canvas id="profitChart" height="200"></canvas>
            </div>
            <div class="card shadow-sm p-3 border-0 mb-4">
                <h5 class="fw-bold mb-3">Purchases Trend</h5>
                <canvas id="purchasesChart" height="200"></canvas>
            </div>
            <div class="card shadow-sm p-3 border-0 mb-4">
                <h5 class="fw-bold mb-3">Top Products</h5>
                <canvas id="topProductsChart" height="200"></canvas>
            </div>
            <div class="card shadow-sm p-3 border-0">
                <h5 class="fw-bold mb-3">Monthly Revenue</h5>
                <canvas id="revenueChart" height="200"></canvas>
            </div>
        </div>

        {{-- Right Side - Tables --}}
        <div class="col-lg-6">
            <!-- Search form -->
            <div class="col-6 ms-auto mb-3">
                <form action="{{ route('dashboard') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control form-control-sm rounded-pill" placeholder="Search...">
                <button type="submit" class="btn btn-sm ms-2" style="background: transparent; border: none;">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            </div>
            <div class="card shadow-sm p-3 border-0 mb-4">
                <h5 class="fw-bold mb-3">Recent Sales</h5>
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr><th>Invoice</th><th>Date</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @foreach($recentSales as $sale)
                        <tr>
                            <td>{{ $sale->invoice_no }}</td>
                            <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('Y-m-d') }}</td>
                            <td>{{ $sale->grand_total }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

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
                            <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('Y-m-d') }}</td>
                            <td>{{ $purchase->total_amount }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card shadow-sm p-3 border-0 mt-4">
                <h5 class="fw-bold mb-3">Top 5 Selling Products</h5>
                <ul class="list-group">
                    @foreach($topProducts as $product)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $product->name }}
                            <span class="badge bg-primary">{{ $product->total_sold }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('sales.create') }}" class="btn btn-primary btn-lg">➕ Add Sale</a>
        <a href="{{ route('purchases.create') }}" class="btn btn-success btn-lg">➕ Add Purchase</a>
        <a href="{{ route('products.create') }}" class="btn btn-warning btn-lg text-white">➕ Add Product</a>
        <a href="{{ route('customers.create') }}" class="btn btn-info btn-lg text-white">➕ Add Customer</a>

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
    // Purchases Trend Chart
    var ctx = document.getElementById('purchasesChart').getContext('2d');
    var purchasesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($purchasesTrend->pluck('date')),
            datasets: [{
                label: 'Purchases',
                data: @json($purchasesTrend->pluck('total')),
                borderColor: 'rgba(76, 108, 215, 1)',
                backgroundColor: 'rgba(76, 108, 215, 0.3)',
                tension: 0.3,
                fill: true
            }]
        }
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

    // Revenue Chart
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: @json($dates),
            datasets: [{
                label: 'Revenue',
                data: @json($revenue),
                borderColor: 'rgba(9, 96, 62, 1)',
                backgroundColor: 'rgba(9, 96, 62, 0.3)',
                tension: 0.3,
                fill: true
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Profit Chart
    new Chart(document.getElementById('profitChart'), {
        type: 'line',
        data: {
            labels: @json($dates),
            datasets: [{
                label: 'Profit',
                data: @json($profit),
                borderColor: 'rgba(163, 28, 35, 1)',
                backgroundColor: 'rgba(163, 28, 35, 0.2)',
                tension: 0.3,
                fill: true
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
</script>
@endpush
