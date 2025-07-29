@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Sales List</h2>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('sales.create') }}" class="btn btn-primary mb-3">Create New Sale</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Invoice No</th>
                <th>Sale Date</th>
                <th>Customer</th>
                <th>Grand Total</th>
                <th>Paid</th>
                <th>Due</th>
                <th>Payment Method</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
            <tr>
                <td>{{ $sale->invoice_no }}</td>
                <td>{{ $sale->sale_date->format('Y-m-d') }}</td>
                <td>{{ $sale->customer ? $sale->customer->name : 'Walk-in' }}</td>
                <td>{{ number_format($sale->grand_total, 2) }}</td>
                <td>{{ number_format($sale->paid_amount, 2) }}</td>
                <td>{{ number_format($sale->due_amount, 2) }}</td>
                <td>{{ $sale->payment_method }}</td>
                <td>
                    <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-info btn-sm">View</a>
                    <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure want to delete?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No sales found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div>
        {{ $sales->links() }}
    </div>
</div>
@endsection
