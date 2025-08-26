@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Transaction Logs</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" class="mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-auto">
                <select name="account_id" class="form-select">
                    <option value="">-- Select Account --</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                            {{ $account->account_name ?? 'Account ' . $account->id }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <input type="text" name="transaction_type" class="form-control" placeholder="Transaction Type" value="{{ request('transaction_type') }}">
            </div>
            <div class="form-floating col-auto">
                <input type="date" class="form-control" id="date_from" name="date_from" placeholder="From Date" value="{{ request('date_from') }}">
                <label for="date_from" class="border-2">From Date</label>
            </div>

            <div class="form-floating col-auto">
                <input type="date" class="form-control" id="date_to" name="date_to" placeholder="To Date" value="{{ request('date_to') }}">
                <label for="date_to"class="border-2">To Date</label>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('transaction_logs.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    <a href="{{ route('transaction_logs.create') }}" class="btn btn-success mb-3">Add New Transaction</a>

    <table class="table table-striped table-bordered align-middle text-center">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Account</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Transaction Type</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $t)
                <tr>
                    <td>{{ $t->id }}</td>
                    <td>{{ $t->transaction_date->format('Y-m-d') }}</td>
                    <td>{{ $t->account->account_name ?? 'N/A' }}</td>
                    <td>{{ ucfirst($t->type) }}</td>
                    <td>{{ number_format($t->amount, 2) }}</td>
                    <td>{{ ucfirst($t->transaction_type) }}</td>
                    <td>{{ $t->description }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No transactions found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $transactions->withQueryString()->links() }}
</div>
@endsection
