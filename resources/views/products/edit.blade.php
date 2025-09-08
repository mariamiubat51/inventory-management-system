@extends('layouts.app')

@section('content')
<div class="container">
  <h2 class="mb-4">Edit Product</h2>

  {{-- Show validation errors --}}
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label class="form-label">Product Name</label>
      <input type="text" name="name" value="{{ $product->name }}" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="category" class="form-label">Category</label>
        <select name="category_id" id="category" class="form-select" required>
            <option value="">-- Select Category --</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" 
                    {{ $product->category_id == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label for="unit" class="form-label">Unit</label>
        <select name="unit_id" id="unit" class="form-select" required>
            <option value="">-- Select Unit --</option>
            @foreach($units as $unit)
                <option value="{{ $unit->id }}"
                    {{ (old('unit_id', $default_unit_id ?? '') == $unit->id) ? 'selected' : '' }}>
                    {{ $unit->name }} ({{ $unit->symbol }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control">{{ $product->description }}</textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Buying Price (৳)</label>
      <input type="number" step="0.01" name="buying_price" value="{{ $product->buying_price }}" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Selling Price (৳)</label>
      <input type="number" step="0.01" name="selling_price" value="{{ $product->selling_price }}" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Stock Quantity</label>
      <input type="number" name="stock_quantity" value="{{ $product->stock_quantity }}" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Product Image</label>
      <input type="file" name="image" class="form-control">

      @if ($product->image)
        <div class="mt-2">
          <img src="{{ asset('storage/' . $product->image) }}" alt="Product Image" width="100">
        </div>
      @endif
    </div>

    <button type="submit" class="btn btn-primary">
      <i class="fas fa-save me-1"></i> Update Product
    </button>
    <a href="{{ route('products.index') }}" class="btn btn-secondary ms-2">Cancel</a>
  </form>
</div>
@endsection
