@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Expense List</h2>

    <!-- Display Validation Errors -->
@if ($errors->any())
    <div class="alert alert-danger mt-3">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="GET" action="{{ route('expenses.index') }}" class="mb-3">
    <div class="row">
        <!-- Category Search -->
        <div class="col-md-3">
            <label for="category_name">Category</label>
            <input type="text" name="category_name" class="form-control"
                   value="{{ request('category_name') }}" autocomplete="off">
        </div>

        <!-- From Date -->
        <div class="col-md-3">
            <label for="from_date">From Date</label>
            <input type="date" name="from_date" class="form-control"
                   value="{{ request('from_date') }}">
        </div>

        <!-- To Date -->
        <div class="col-md-3">
            <label for="to_date">To Date</label>
            <input type="date" name="to_date" class="form-control"
                   value="{{ request('to_date') }}">
        </div>

        <!-- Buttons -->
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-info m-1">Search</button>
            <a href="{{ route('expenses.index') }}" class="btn btn-secondary m-1">Reset</a>
        </div>
    </div>
</form>


    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('expenses.create') }}" class="btn btn-primary mb-3">+ Add Expense</a>

    <table class="table table-striped table-bordered align-middle text-center">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Title</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Account</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($expenses as $expense)
            <tr>
                <td>{{ $expense->id }}</td>
                <td>{{ $expense->date }}</td>
                <td>{{ $expense->title }}</td>
                <td>{{ $expense->category->name }}</td>
                <td>{{ number_format($expense->amount, 2) }}</td>
                <td>{{ $expense->account->account_name ?? 'N/A' }}</td>
                <td>
                    <a href="{{ route('expenses.edit', $expense->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" onclick="return confirm('Are you sure?')" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6">No expenses found.</td></tr>
        @endforelse
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $expenses->links() }}
    </div>
</div>
@endsection
