@extends('layouts.app')

@section('content')
<h2>Low Stock Report</h2>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Product</th>
            <th>Stock Qty</th>
            <th>Reorder Level</th>
        </tr>
    </thead>
    <tbody>
        @foreach($products as $product)
        <tr>
            <td>{{ $product->name }}</td>
            <td>{{ $product->stock_qty }}</td>
            <td>{{ $product->reorder_level }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
