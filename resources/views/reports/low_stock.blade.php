@extends('layouts.app')

@section('content')
<div class="container">
    <h3>⚠️ Low Stock Products</h3>
    <table class="table table-striped table-bordered align-middle text-center">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Stock Qty</th>
                <th>Reorder Level</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lowStockProducts as $product)
                <tr class="table-warning">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->stock_quantity }}</td>
                    <td>{{ $lowStockAlert }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
