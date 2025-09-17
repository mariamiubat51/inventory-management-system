@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Transaction Logs</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

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

    <form method="GET" action="{{ route('transaction_logs.index') }}" class="mb-3">
        <div class="row">
            <!-- Account -->
            <div class="col-md-3">
                <label for="account_id">Account</label>
                <select name="account_id" id="account_id" class="form-control">
                    <option value="">-- Select Account --</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                            {{ $account->account_name ?? 'Account ' . $account->id }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Transaction Type -->
            <div class="col-md-2">
                <label for="transaction_type">Transaction Type</label>
                <input type="text" name="transaction_type" id="transaction_type" class="form-control" 
                    value="{{ request('transaction_type') }}" autocomplete="off">
            </div>

            <!-- From Date -->
            <div class="col-md-2">
                <label for="date_from">From Date</label>
                <input type="date" name="date_from" id="date_from" class="form-control" 
                    value="{{ request('date_from') }}">
            </div>

            <!-- To Date -->
            <div class="col-md-2">
                <label for="date_to">To Date</label>
                <input type="date" name="date_to" id="date_to" class="form-control" 
                    value="{{ request('date_to') }}">
            </div>

            <!-- Buttons -->
            <div class="col-md-3 d-flex align-items-end mt-2">
                <button type="submit" class="btn btn-info m-1">Search</button>
                <a href="{{ route('transaction_logs.index') }}" class="btn btn-secondary m-1">Reset</a>
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
                    <td>{{ $t->type_label }}</td>
                    <td>{{ number_format($t->amount, 2) }}</td>
                    <td>{{ $t->transaction_type_label }}</td>
                    <td>{{ $t->description }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No transactions found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div>
        {{ $transactions->links() }}
    </div>
</div>
@endsection
