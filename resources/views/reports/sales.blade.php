@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Sales Report</h2>

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
                    <h5 class="card-title">Total Paid</h5>
                    <p class="card-text fs-4 fw-bold">{{ number_format($totalPaid, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card text-white bg-danger shadow">
                <div class="card-body">
                    <h5 class="card-title">Total Due</h5>
                    <p class="card-text fs-4 fw-bold">{{ number_format($totalDue, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Filter Report</h6>
        </div>
        <div class="card-body">
            <form method="GET">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2">
                        <label for="from_date">From Date</label>
                        <input type="date" id="from_date" name="from_date" class="form-control" value="{{ request('from_date', now()->startOfMonth()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label for="to_date">To Date</label>
                        <input type="date" id="to_date" name="to_date" class="form-control" value="{{ request('to_date', now()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label for="customer">Customer</label>
                        <select name="customer_id" id="customer" class="form-control">
                            <option value="">All Customers</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-primary w-100">Apply Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Sales Trend</h6>
        </div>
        <div class="card-body">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Sales Details</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="salesDataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Invoice No</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Grand Total</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td>{{ $sale->id }}</td>
                            <td>{{ $sale->invoice_no }}</td>
                            <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M, Y') }}</td>
                            <td>{{ $sale->customer->name ?? 'Walk-in Customer' }}</td>
                            <td>{{ number_format($sale->grand_total, 2) }}</td>
                            <td>{{ number_format($sale->paid_amount, 2) }}</td>
                            <td>{{ number_format($sale->due_amount, 2) }}</td>
                            <td>
                                @if($sale->due_amount <= 0)
                                    <span class="badge bg-success">Paid</span>
                                @else
                                    <span class="badge bg-danger">Due</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No sales found.</td>
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
    const ctx = document.getElementById('salesChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels), // ✅ Use data from controller
            datasets: [{
                label: 'Sales',
                data: @json($chartValues), // ✅ Use data from controller
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // DataTables.js
    $(document).ready(function() {
        $('#salesDataTable').DataTable();
    });
</script>
@endpush