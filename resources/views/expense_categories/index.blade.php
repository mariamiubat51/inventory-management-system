@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Expense Categories
        <a href="{{ route('expense-categories.create') }}" class="btn btn-primary float-end">Add New</a>
    </h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($categories->count())
    <table class="table table-striped table-bordered align-middle text-center">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $category)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $category->name }}</td>
                <td>
                    <a href="{{ route('expense-categories.edit', $category->id) }}" class="btn btn-sm btn-warning">Edit</a>

                    <form action="{{ route('expense-categories.destroy', $category->id) }}" method="POST" style="display:inline-block">
                        @csrf
                        @method('DELETE')
                        <button onclick="return confirm('Are you sure to delete this category?')" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>No categories found.</p>
    @endif

    <!-- Pagination -->
     <div class="mt-3">
        {{ $categories->links() }}
     </div>
</div>
@endsection
