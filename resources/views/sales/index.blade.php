@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Sales List</h2>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('sales.create') }}" class="btn btn-primary mb-3">Create New Sale</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Invoice No</th>
                <th>Sale Date</th>
                <th>Customer</th>
                <th>Grand Total</th>
                <th>Paid</th>
                <th>Due</th>
                <th>Payment Method</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
            <tr>
                <td>{{ $sale->id }}</td>
                <td>{{ $sale->invoice_no }}</td>
                <td>{{ $sale->sale_date->format('Y-m-d') }}</td>
                <td>{{ $sale->customer ? $sale->customer->name : 'Walk-in' }}</td>
                <td>{{ number_format($sale->grand_total, 2) }}</td>
                <td>{{ number_format($sale->paid_amount, 2) }}</td>
                <td>{{ number_format($sale->due_amount, 2) }}</td>
                <td>{{ $sale->payment_method }}</td>
                <td>
                    <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-info btn-sm">View</a>
                    <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-warning btn-sm">Edit</a>

                    @if($sale->due_amount > 0)
                        <button class="btn btn-secondary btn-sm mt-1" data-bs-toggle="modal" data-bs-target="#payDueModal{{ $sale->id }}">
                            💰 Pay Due
                        </button>
                    @endif

                    <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure want to delete?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm mt-1" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No sales found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @foreach ($sales as $sale)
    @if($sale->due_amount > 0)
    <!-- Pay Due Modal -->
    <div class="modal fade" id="payDueModal{{ $sale->id }}" tabindex="-1" aria-labelledby="payDueModalLabel{{ $sale->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('sales.payDue', $sale->id) }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="payDueModalLabel{{ $sale->id }}">Pay Due - Invoice #{{ $sale->invoice_no }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Due Amount: <strong>{{ number_format($sale->due_amount, 2) }}৳</strong></p>

                        <div class="mb-3">
                            <label>Select Account</label>
                            <select name="account_id" class="form-select" required>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">
                                        {{ $account->account_name }} ({{ number_format($account->total_balance, 2) }}৳)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Pay Amount</label>
                            <input type="number" name="pay_amount" class="form-control"
                                max="{{ $sale->due_amount }}" value="{{ $sale->due_amount }}" step="0.01" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Pay</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
    @endforeach

    <div>
        {{ $sales->links() }}
    </div>
</div>
@endsection
