@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Add Stock Movement</h2>

    <form action="{{ route('stock_movements.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="product_id" class="form-label">Product</label>
            <select name="product_id" id="product_id" class="form-control" required>
                <option value="">-- Select Product --</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}">{{ $p->id }} - {{ $p->name }} (Current: {{ $p->stock }})</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="movement_type" class="form-label">Movement Type</label>
            <select name="movement_type" id="movement_type" class="form-control" required>
                <option value="in">Stock In</option>
                <option value="out">Stock Out</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" name="quantity" id="quantity" class="form-control" min="1" max="{{ $p->stock }}" required>
        </div>

        <div class="mb-3">
            <label for="reference" class="form-label">Reference (Invoice/PO/etc.)</label>
            <input type="text" name="reference" id="reference" class="form-control">
        </div>

        <div class="mb-3">
            <label for="remarks" class="form-label">Remarks</label>
            <textarea name="remarks" id="remarks" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Save</button>
        <a href="{{ route('stock_movements.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
