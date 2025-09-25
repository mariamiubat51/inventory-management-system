@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Ledger for Account: <strong>{{ $account->account_name }}</strong></h3>
    <a href="{{ route('accounts.index') }}" class="btn btn-secondary mb-3">Back</a>

    @if(count($entries))
    <table class="table table-striped table-bordered align-middle text-center">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entries as $entry)
            <tr>
                <td>{{ $loop->count - $loop->index }}</td>
                <td>{{ $entry->transaction_date->format('Y-m-d') }}</td>
                <td>{{ ucfirst($entry->type) }}</td>
                <td class="{{ $entry->type == 'debit' ? 'text-danger' : 'text-success' }}">
                    {{ number_format($entry->amount, 2) }}
                </td>
                <td>{{ $entry->description }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p>No ledger entries found for this account.</p>
    @endif
</div>
@endsection
