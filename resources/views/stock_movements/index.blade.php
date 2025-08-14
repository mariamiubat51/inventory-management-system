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

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>Product</th>
                <th>Type</th>
                <th>Quantity</th>
                <th>Balance</th>
                <th>Reference</th>
                <th>Performed By</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movements as $m)
            <tr>
                <td>{{ $m->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $m->product->name }}</td>
                <td>{{ ucfirst($m->movement_type) }}</td>
                <td>{{ $m->quantity }}</td>
                <td>{{ $m->balance }}</td>
                <td>{{ $m->reference }}</td>
                <td>{{ $m->user->name }}</td>
                <td>{{ $m->remarks }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
