@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Add Supplier</h2>

    <form action="{{ route('suppliers.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Name *</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Phone *</label>
            <input type="text" name="phone" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control">
        </div>
        <div class="mb-3">
            <label>Address</label>
            <textarea name="address" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label>Company Name</label>
            <input type="text" name="company_name" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Save Supplier</button>
    </form>
</div>
@endsection
