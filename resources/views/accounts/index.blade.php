@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Accounts List</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Search Form --}}
    <form method="GET" action="{{ route('accounts.index') }}" class="row g-3 mb-3">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by name or type" value="{{ request('search') }}">
        </div>
        <div class="col-md-auto">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="{{ route('accounts.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <a href="{{ route('accounts.create') }}" class="btn btn-success mb-3">Add New Account</a>

    <table class="table table-striped table-bordered align-middle text-center">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Account Code</th>
                <th>Name</th>
                <th>Type</th>
                <th>Opening Balance</th>
                <th>Total Balance</th>
                <th>Note</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($accounts as $account)
            <tr>
                <td>{{ $account->id }}</td>
                <td>{{ $account->account_code }}</td>
                <td>{{ $account->account_name }}</td>
                <td>{{ ucfirst($account->account_type) }}</td>
                <td>{{ number_format($account->initial_balance, 2) }}</td>
                <td>{{ number_format($account->total_balance, 2) }}</td>
                <td>{{ $account->note }}</td>
                <td>
                    <a href="{{ route('accounts.edit', $account->id) }}" class="btn btn-sm btn-warning">Edit</a>

                    <a href="{{ route('accounts.ledger', $account->id) }}" class="btn btn-sm btn-info">Ledger</a>

                    <form action="{{ route('accounts.destroy', $account->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button onclick="return confirm('Delete this account?')" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No accounts found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3">
        {{ $accounts->links() }}
    </div>
</div>
@endsection
