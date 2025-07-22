@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Product List</h2>
    <a href="{{ route('products.create') }}" class="btn btn-primary">
      <i class="fas fa-plus"></i> Add New Product
    </a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
  @endif

  <table class="table table-striped table-bordered align-middle text-center">
    <thead class="table-dark">
      <tr>
        <th>#ID</th>
        <th>Code</th>
        <th>Image</th>
        <th>Name</th>
        <th>Description</th>
        <th>Buying Price (৳)</th>
        <th>Selling Price (৳)</th>
        <th>Stock</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($products as $product)
      <tr>
        <td>{{ $product->id }}</td>
        <td>{{ $product->product_code }}</td>

        {{-- Show product image --}}
        <td>
          @if($product->image)
            <img src="{{ asset('storage/' . $product->image) }}" width="60" height="60" style="object-fit: cover;" alt="Product Image">
          @else
            <span class="text-muted">No image</span>
          @endif
        </td>

        <td>{{ $product->name }}</td>
        <td>{{ $product->description }}</td>
        <td>{{ $product->buying_price }}</td>
        <td>{{ $product->selling_price }}</td>
        <td>{{ $product->stock_quantity }}</td>
        <td>
          <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
          <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
              <i class="fas fa-trash"></i>
            </button>
          </form>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="9" class="text-center">No products found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
