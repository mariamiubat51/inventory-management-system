@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Purchase Report</h2>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card text-white bg-primary shadow">
                <div class="card-body">
                    <h5 class="card-title">Total Purchases</h5>
                    <p class="card-text fs-4 fw-bold">{{ number_format($totalPurchases, 2) }}</p>
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
                        <label for="supplier">Supplier</label>
                        <select name="supplier_id" id="supplier" class="form-control">
                            <option value="">All Suppliers</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
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

    <!-- Chart Section -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Purchase Trend</h6>
        </div>
        <div class="card-body">
            <canvas id="purchaseChart"></canvas>
        </div>
    </div>

    <!-- Details Table -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Purchase Details</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="purchaseDataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Invoice No</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Total Amount</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->id }}</td>
                            <td>{{ $purchase->invoice_no }}</td>
                            <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M, Y') }}</td>
                            <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>
                            <td>{{ number_format($purchase->total_amount, 2) }}</td>
                            <td>{{ number_format($purchase->paid_amount, 2) }}</td>
                            <td>{{ number_format($purchase->due_amount, 2) }}</td>
                            <td>
                                @if($purchase->due_amount <= 0)
                                    <span class="badge bg-success">Paid</span>
                                @else
                                    <span class="badge bg-danger">Due</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No purchases found.</td>
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
    const ctx = document.getElementById('purchaseChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels), // ✅ From controller
            datasets: [{
                label: 'Purchases',
                data: @json($chartValues), // ✅ From controller
                borderColor: 'rgb(54, 162, 235)',
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
        $('#purchaseDataTable').DataTable();
    });
</script>
@endpush
