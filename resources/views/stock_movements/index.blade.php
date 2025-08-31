@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Stock Movements</h2>

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
