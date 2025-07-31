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
            <h4>Sale Invoice</h4>
            <a href="{{ route('sales.index') }}" class="btn btn-secondary btn-sm">Back</a>
        </div>

        <div class="card-body">
            <div class="mb-4">
                <strong>Invoice No:</strong> {{ $sale->invoice_no }}<br>
                <strong>Sale Date:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('d M, Y') }}<br>
                <strong>Customer:</strong> {{ $sale->customer->name ?? 'Walk-in Customer' }}
            </div>

            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $key => $item)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $item->product->name ?? 'Deleted Product' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->price, 2) }}</td>
                        <td>{{ number_format($item->quantity * $item->price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="row mt-4">
                <div class="col-md-6">
                    <strong>Note:</strong><br>
                    {{ $sale->note ?? '-' }}
                </div>
                <div class="col-md-6">
                    <table class="table">
                        <tr>
                            <th>Subtotal:</th>
                            <td>{{ number_format($sale->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Discount:</th>
                            <td>{{ number_format($sale->discount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Grand Total:</th>
                            <td>{{ number_format($sale->grand_total, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Paid:</th>
                            <td>{{ number_format($sale->paid_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Due:</th>
                            <td>{{ number_format($sale->due_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Payment Method:</th>
                            <td>{{ $sale->payment_method }}</td>
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
