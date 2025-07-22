@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Supplier</h2>

    <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label>Name *</label>
            <input type="text" name="name" class="form-control" value="{{ $supplier->name }}" required>
        </div>
        <div class="mb-3">
            <label>Phone *</label>
            <input type="text" name="phone" class="form-control" value="{{ $supplier->phone }}" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ $supplier->email }}">
        </div>
        <div class="mb-3">
            <label>Address</label>
            <textarea name="address" class="form-control">{{ $supplier->address }}</textarea>
        </div>
        <div class="mb-3">
            <label>Company Name</label>
            <input type="text" name="company_name" class="form-control" value="{{ $supplier->company_name }}">
        </div>
        <button type="submit" class="btn btn-primary">Update Supplier</button>
    </form>
</div>
@endsection
