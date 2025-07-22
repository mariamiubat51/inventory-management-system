@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Suppliers</h2>
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Add Supplier</a>
</div>


    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-striped table-bordered align-middle text-center">
        <thead class="table-dark">
            <tr>
                <th>ID</th>              
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Address</th>
                <th>Company</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($suppliers as $supplier)
            <tr>
                <td>{{ $supplier->id }}</td>       
                <td>{{ $supplier->name }}</td>
                <td>{{ $supplier->phone }}</td>
                <td>{{ $supplier->email }}</td>
                <td>{{ $supplier->address }}</td>
                <td>{{ $supplier->company_name }}</td>
                <td>
                    <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('Delete this supplier?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
