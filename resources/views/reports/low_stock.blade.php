@extends('layouts.app')

@section('content')
<div class="container">
    <h3>⚠️ Low Stock Products</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Product</th>
                <th>Stock Qty</th>
                <th>Reorder Level</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lowStockProducts as $product)
                <tr class="table-warning">
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->stock_quantity }}</td>
                    <td>{{ $product->reorder_level }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
