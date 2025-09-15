@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Stock Movements</h2>

    <form method="GET" action="{{ route('stock_movements.index') }}" class="mb-3">
        <div class="row">
            <!-- Movement Type Filter -->
            <div class="col-md-3">
                <label for="movement_type">Movement Type</label>
                <select name="movement_type" class="form-control">
                    <option value="">-- All Movements --</option>
                    <option value="in" {{ request('movement_type') == 'in' ? 'selected' : '' }}>In</option>
                    <option value="out" {{ request('movement_type') == 'out' ? 'selected' : '' }}>Out</option>
                </select>
            </div>

            <!-- From Date Filter -->
            <div class="col-md-3">
                <label for="from_date">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>

            <!-- To Date Filter -->
            <div class="col-md-3">
                <label for="to_date">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>

            <!-- Search and Reset Buttons -->
            <div class="col-md-3 d-flex align-items-end">
                <!-- Search Button -->
                <button type="submit" class="btn btn-info mr-2 m-1">Search</button>

                <!-- Reset Button -->
                <a href="{{ route('stock_movements.index') }}" class="btn btn-secondary m-1">Reset</a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger mt-3">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </form>


    <a href="{{ route('stock_movements.create') }}" class="btn btn-primary mb-3">Add Stock Movement</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-striped table-bordered align-middle text-center">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Movement Type</th>
                <th>Quantity</th>
                <th>Current Stock</th>
                <th>Reference</th>
                <th>User ID</th>
                <th>Performed By</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movements as $m)
            <tr>
                <td>{{ $m->id }}</td>
                <td>{{ $m->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $m->product->id }}</td>
                <td>{{ $m->product->name }}</td>
                <td>{{ ucfirst($m->movement_type) }}</td>
                <td>{{ $m->quantity }}</td>
                <td>{{ $m->balance }}</td> {{-- or $m->current_stock if you renamed --}}
                <td>{{ $m->reference }}</td>
                <td>{{ $m->user->id }}</td>
                <td>{{ $m->user->name }}</td>
                <td>{{ $m->remarks }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-3">
        {{ $movements->links() }}
    </div>
</div>
@endsection
