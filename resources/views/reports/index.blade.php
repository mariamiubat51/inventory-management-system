@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Reports Dashboard</h2>

    <div class="row">
        <!-- Sales -->
        <div class="col-md-4 mb-3">
            <div class="card text-white bg-primary h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Sales</h5>
                    <h3>{{ number_format($totalSales, 2) }} BDT</h3>
                    <a href="{{ route('sales.index') }}" class="btn btn-light btn-sm mt-2">View Details</a>
                </div>
            </div>
        </div>

        <!-- Purchases -->
        <div class="col-md-4 mb-3">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Purchases</h5>
                    <h3>{{ number_format($totalPurchases, 2) }} BDT</h3>
                    <a href="{{ route('purchases.index') }}" class="btn btn-light btn-sm mt-2">View Details</a>
                </div>
            </div>
        </div>

        <!-- Expenses -->
        <div class="col-md-4 mb-3">
            <div class="card text-white bg-warning h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Expenses</h5>
                    <h3>{{ number_format($totalExpenses, 2) }} BDT</h3>
                    <a href="{{ route('expenses.index') }}" class="btn btn-light btn-sm mt-2">View Details</a>
                </div>
            </div>
        </div>

        <!-- Stock Value -->
        <div class="col-md-4 mb-3">
            <div class="card text-white bg-info h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Stock Value</h5>
                    <h3>{{ number_format($totalStockValue, 2) }} BDT</h3>
                    <a href="{{ route('stock.index') }}" class="btn btn-light btn-sm mt-2">View Details</a>
                </div>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="col-md-4 mb-3">
            <div class="card text-white bg-danger h-100">
                <div class="card-body">
                    <h5 class="card-title">Low Stock Products</h5>
                    <h3>{{ $lowStockProducts }}</h3>
                    <a href="{{ route('stock.low') }}" class="btn btn-light btn-sm mt-2">View Details</a>
                </div>
            </div>
        </div>

        <!-- Profit -->
        <div class="col-md-4 mb-3">
            <div class="card text-white bg-secondary h-100">
                <div class="card-body">
                    <h5 class="card-title">Profit</h5>
                    @php
                        $grossProfit = $totalSales - $totalPurchases;
                        $netProfit = $grossProfit - $totalExpenses;
                    @endphp
                    <h3>{{ number_format($netProfit, 2) }} BDT</h3>
                    <a href="{{ route('reports.profit') }}" class="btn btn-light btn-sm mt-2">View Details</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Optional: Add mini charts here using Chart.js or any library -->
    <div class="row mt-4">
        <div class="col-md-6">
            <canvas id="salesChart"></canvas>
        </div>
        <div class="col-md-6">
            <canvas id="expensesChart"></canvas>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const salesChart = new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'], // example, replace dynamically
            datasets: [{
                label: 'Sales',
                data: [12000, 15000, 14000, 18000, 20000],
                borderColor: 'rgba(0,123,255,1)',
                backgroundColor: 'rgba(0,123,255,0.2)',
                fill: true
            }]
        }
    });

    const expensesChart = new Chart(document.getElementById('expensesChart'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
            datasets: [{
                label: 'Expenses',
                data: [8000, 7000, 9000, 10000, 9500],
                borderColor: 'rgba(255,193,7,1)',
                backgroundColor: 'rgba(255,193,7,0.2)',
                fill: true
            }]
        }
    });
</script>
@endsection
