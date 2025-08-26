@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Profit Report</h2>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card text-white bg-primary shadow">
                <div class="card-body">
                    <h5 class="card-title">Total Sales</h5>
                    <p class="card-text fs-4 fw-bold">{{ number_format($totalSales, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card text-white bg-success shadow">
                <div class="card-body">
                    <h5 class="card-title">COGS</h5>
                    <p class="card-text fs-4 fw-bold">{{ number_format($totalCOGS, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card text-white bg-warning shadow">
                <div class="card-body">
                    <h5 class="card-title">Gross Profit</h5>
                    <p class="card-text fs-4 fw-bold">{{ number_format($grossProfit, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card text-white bg-danger shadow">
                <div class="card-body">
                    <h5 class="card-title">Expenses</h5>
                    <p class="card-text fs-4 fw-bold">{{ number_format($totalExpenses, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card text-white bg-info shadow">
                <div class="card-body">
                    <h5 class="card-title">Net Profit</h5>
                    <p class="card-text fs-4 fw-bold">{{ number_format($netProfit, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Filter Report</h6>
        </div>
        <div class="card-body">
            <form method="GET">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2">
                        <label for="from_date">From Date</label>
                        <input type="date" id="from_date" name="from_date" class="form-control"
                            value="{{ request('from_date', now()->startOfMonth()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label for="to_date">To Date</label>
                        <input type="date" id="to_date" name="to_date" class="form-control"
                            value="{{ request('to_date', now()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-primary w-100">Apply Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Profit Trend</h6>
        </div>
        <div class="card-body">
            <canvas id="profitChart"></canvas>
        </div>
    </div>

    <!-- Details Table -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Profit Details</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="profitDataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Invoice No</th>
                            <th>Sale Date</th>
                            <th>Total Sales</th>
                            <th>COGS</th>
                            <th>Gross Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        @php
                            $cogs = $sale->items ? $sale->items->sum(fn($item) => $item->quantity * ($item->product->buying_price ?? 0)) : 0;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $sale->invoice_no }}</td>
                            <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M, Y') }}</td>
                            <td>{{ number_format($sale->grand_total, 2) }}</td>
                            <td>{{ number_format($cogs, 2) }}</td>
                            <td>{{ number_format($sale->grand_total - $cogs, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No sales found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    // Chart.js with DYNAMIC data
    const ctx = document.getElementById('profitChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels), // From controller
            datasets: [{
                label: 'Profit',
                data: @json($chartProfit), // From controller
                borderColor: 'rgb(54, 162, 235)',
                tension: 0.1
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // DataTables.js
    $(document).ready(function() {
        $('#profitDataTable').DataTable();
    });
</script>
@endpush
