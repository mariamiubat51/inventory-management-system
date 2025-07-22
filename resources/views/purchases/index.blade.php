@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Purchase List</h2>

    <a href="{{ route('purchases.create') }}" class="btn btn-success mb-3" style="background-color: rgba(95, 58, 58, 1);">➕ Add Purchase</a>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Invoice No</th>
                <th>Supplier</th>
                <th>Date</th>
                <th>Items</th>
                <th>Total</th>
                <th>Paid</th>
                <th>Due</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($purchases as $purchase)
            <tr>
                <td>{{ $purchase->id }}</td>
                <td>{{ $purchase->invoice_no }}</td>
                <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>
                <td>{{ $purchase->purchase_date->format('Y-m-d') }}</td>
                <td>{{ $purchase->items_count }} items</td>
                <td>{{ number_format($purchase->total_amount, 2) }}৳</td>
                <td>{{ number_format($purchase->paid_amount, 2) }}৳</td>
                <td>{{ number_format($purchase->due_amount, 2) }}৳</td>
                <td>
                    @if($purchase->payment_status == 'fully_paid')
                        <span class="badge bg-success">Fully Paid</span>
                    @elseif($purchase->payment_status == 'partially_paid')
                        <span class="badge bg-warning text-dark">Partially Paid</span>
                    @else
                        <span class="badge bg-danger">Unpaid</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-info">👁 View</a>
                    <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-sm btn-warning">✏️ Edit</a>
                    <form action="{{ route('purchases.destroy', $purchase->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this purchase?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">🗑️ Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">No purchases found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
