@extends('layouts.app')

@section('content')

<style>
    @media print {
        .btn, .card-header, .card-footer {
            display: none !important;
        }
    }
</style>

<div class="container">
    <div class="card shadow rounded">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Purchase Invoice</h4>
            <a href="{{ route('purchases.index') }}" class="btn btn-secondary btn-sm">Back</a>
        </div>

        <div class="card-body">
            <div class="mb-4">
                <strong>Invoice No:</strong> {{ $purchase->invoice_no }}<br>
                <strong>Purchase Date:</strong> {{ $purchase->purchase_date->format('d M, Y') }}<br>
                <strong>Supplier:</strong> {{ $purchase->supplier->name ?? 'N/A' }}
            </div>

            <table class="table table-striped table-bordered align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Buying Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase->items as $key => $item)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $item->product->name ?? 'Deleted Product' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->buying_price, 2) }}৳</td>
                        <td>{{ number_format($item->subtotal, 2) }}৳</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="row mt-4">
                <div class="col-md-6">
                    <strong>Notes:</strong><br>
                    {{ $purchase->notes ?? '-' }}
                </div>
                <div class="col-md-6">
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
            </div>

            <div class="text-center mt-4">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fa fa-print"></i> Print Invoice
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
