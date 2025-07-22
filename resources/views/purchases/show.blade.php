
@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Purchase Invoice - {{ $purchase->invoice_no }}</h2>

    <div class="mb-3">
        <strong>Supplier:</strong> {{ $purchase->supplier->name ?? 'N/A' }}<br>
        <strong>Date:</strong> {{ $purchase->purchase_date->format('Y-m-d') }}<br>
        <strong>Items Purchased:</strong> {{ $purchase->items->count() }}<br>
        <strong>Notes:</strong> {{ $purchase->notes ?? 'None' }}
    </div>

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Buying Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchase->items as $item)
            <tr>
                <td>{{ $item->product->name ?? 'N/A' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->buying_price, 2) }}৳</td>
                <td>{{ number_format($item->subtotal, 2) }}৳</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="float-end" style="max-width: 300px;">
        <table class="table">
            <tr>
                <th>Total Amount:</th>
                <td>{{ number_format($purchase->total_amount, 2) }}৳</td>
            </tr>
            <tr>
                <th>Paid Amount:</th>
                <td>{{ number_format($purchase->paid_amount, 2) }}৳</td>
            </tr>
            <tr>
                <th>Due Amount:</th>
                <td>{{ number_format($purchase->due_amount, 2) }}৳</td>
            </tr>
            <tr>
                <th>Payment Status:</th>
                <td>
                    @if($purchase->payment_status == 'fully_paid')
                        <span class="badge bg-success">Fully Paid</span>
                    @elseif($purchase->payment_status == 'partially_paid')
                        <span class="badge bg-warning text-dark">Partially Paid</span>
                    @else
                        <span class="badge bg-danger">Unpaid</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="clearfix"></div>

    <a href="{{ route('purchases.index') }}" class="btn btn-secondary mt-3">← Back to Purchases</a>
    <button onclick="window.print()" class="btn btn-primary mt-3">🖨️ Print Invoice</button>
</div>
@endsection
