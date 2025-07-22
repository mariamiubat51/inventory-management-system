@extends('layouts.app')

@section('content')
<div class="container">
  <h2 class="mb-4">Add New Product</h2>

  {{-- Display Validation Errors --}}
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mb-3">
      <label for="name" class="form-label">Product Name</label>
      <input type="text" name="name" id="name" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="description" class="form-label">Description</label>
      <textarea name="description" id="description" class="form-control"></textarea>
    </div>

    <div class="mb-3">
      <label for="buying_price" class="form-label">Buying Price (৳)</label>
      <input type="number" step="0.01" name="buying_price" id="buying_price" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="selling_price" class="form-label">Selling Price (৳)</label>
      <input type="number" step="0.01" name="selling_price" id="selling_price" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="stock_quantity" class="form-label">Stock Quantity</label>
      <input type="number" name="stock_quantity" id="stock_quantity" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="image" class="form-label">Product Image</label>
      <input type="file" name="image" id="image" class="form-control">
    </div>

    <button type="submit" class="btn btn-success">
      <i class="fas fa-check"></i> Save Product
    </button>
  </form>
</div>
@endsection
