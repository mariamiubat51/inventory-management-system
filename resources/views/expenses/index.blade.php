@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Expense List</h2>

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
