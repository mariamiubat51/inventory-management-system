@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Inventory Report</h2>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card text-white bg-primary shadow">
                <div class="card-body">
                    <h5 class="card-title">Total Products</h5>
                    <p class="card-text fs-4 fw-bold">{{ $totalProducts }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card text-white bg-success shadow">
                <div class="card-body">
                    <h5 class="card-title">Total Units In Stock</h5>
                    <p class="card-text fs-4 fw-bold">{{ $totalInStock }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card text-white bg-danger shadow">
                <div class="card-body">
                    <h5 class="card-title">Products Out of Stock</h5>
                    <p class="card-text fs-4 fw-bold">{{ $totalOutOfStock }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-warning text-white shadow">
                <div class="card-body">
                    ⚠️ Low Stock Products: {{ $lowStockCount }}
                </div>
                <a href="{{ route('reports.lowStock') }}" class="card-footer text-white small">
                    View Details →
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Filter Report</h6>
        </div>
        <div class="card-body">
            <form method="GET">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-2">
                        <label for="product_id">Product</label>
                        <select name="product_id" id="product_id" class="form-select">
                            <option value="">All Products</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label for="from_date">From Date</label>
                        <input type="date" id="from_date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label for="to_date">To Date</label>
                        <input type="date" id="to_date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                    </div>
                    <div class="col-md-2 mb-2">
                        <button type="submit" class="btn btn-primary w-100">Apply Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Chart Card -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Current Stock Levels</h6>
        </div>
        <div class="card-body">
            <canvas id="stockChart"></canvas>
        </div>
    </div>

    <!-- Stock Movements Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Stock Movements</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="inventoryDataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Movement Type</th>
                            <th>Quantity</th>
                            <th>Current Quantity</th>
                            <th>Reference</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $i => $movement)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $movement->product->product_code ?? 'N/A' }}</td>
                                <td>{{ $movement->product->name ?? 'N/A' }}</td>
                                <td>
                                    @if($movement->movement_type == 'Sale')
                                        <span class="badge bg-danger">Sale (-)</span>
                                    @elseif($movement->movement_type == 'Purchase')
                                        <span class="badge bg-success">Purchase (+)</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $movement->movement_type }}</span>
                                    @endif
                                </td>
                                <td>{{ $movement->quantity }}</td>
                                <td>{{ $movement->balance }}</td>
                                <td>{{ $movement->reference }}</td>
                                <td>{{ $movement->created_at->format('d M, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No movements found for this period.</td>
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
    // Chart.js
    const ctx = document.getElementById('stockChart').getContext('2d');
    const stockChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Current Stock Quantity',
                data: @json($chartData),
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });

    // DataTables.js
    $(document).ready(function() {
        $('#inventoryDataTable').DataTable();
    });
</script>
@endpush