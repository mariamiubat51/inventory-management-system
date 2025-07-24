@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Add New Transaction</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('transaction_logs.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="transaction_type" class="form-label">Transaction Type</label>
            <input type="text" name="transaction_type" id="transaction_type" class="form-control" required value="{{ old('transaction_type') }}">
            <small class="text-muted">E.g., purchase, payment, refund</small>
        </div>

        <div class="mb-3">
            <label for="related_id" class="form-label">Related ID (optional)</label>
            <input type="number" name="related_id" id="related_id" class="form-control" value="{{ old('related_id') }}">
            <small class="text-muted">E.g., Purchase ID or Payment ID</small>
        </div>

        <div class="mb-3">
            <label for="account_id" class="form-label">Account</label>
            <select name="account_id" id="account_id" class="form-select" required>
                <option value="">-- Select Account --</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>
                        {{ $account->account_name ?? 'Account ' . $account->id }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control" required value="{{ old('amount') }}">
        </div>

        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <select name="type" id="type" class="form-select" required>
                <option value="">-- Select Type --</option>
                <option value="debit" {{ old('type') == 'debit' ? 'selected' : '' }}>Debit</option>
                <option value="credit" {{ old('type') == 'credit' ? 'selected' : '' }}>Credit</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="transaction_date" class="form-label">Transaction Date</label>
            <input type="date" name="transaction_date" id="transaction_date" class="form-control" required value="{{ old('transaction_date', date('Y-m-d')) }}">
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description (optional)</label>
            <textarea name="description" id="description" class="form-control" rows="3">{{ old('description') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Save Transaction</button>
        <a href="{{ route('transaction_logs.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection

