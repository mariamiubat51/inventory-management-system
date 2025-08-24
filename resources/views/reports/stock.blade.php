@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Stock Report</h1>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Category</th>
                <th>Stock Quantity</th>
                <th>Unit Price</th>
                <th>Total Stock Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category->name ?? 'N/A' }}</td>
                <td>{{ $product->stock_qty }}</td>
                <td>{{ number_format($product->price, 2) }}</td>
                <td>{{ number_format($product->stock_qty * $product->price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
