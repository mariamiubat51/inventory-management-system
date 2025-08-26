@extends('layouts.app')

@section('content')
 <div class="container">
    <h2 class="mb-4">Sales List</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('sales.create') }}" class="btn text-white mb-3" style="background-color: rgba(51, 106, 202, 1);">
        ➕ Create New Sale
    </a>

    <table class="table table-striped table-bordered align-middle text-center">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Invoice No</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Items</th>
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
                <td>{{ $sale->customer ? $sale->customer->name : 'Walk-in' }}</td>
                <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('Y-m-d') }}</td>
                <td>{{ $sale->items->count() }} items</td>
                <td>{{ number_format($sale->grand_total, 2) }}৳</td>
                <td>{{ number_format($sale->paid_amount, 2) }}৳</td>
                <td>{{ number_format($sale->due_amount, 2) }}৳</td>
                <td>{{ $sale->payment_method }}</td>
                <td>
                    <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-info mt-1">
                        <i class="fas fa-eye"></i> View
                    </a>

                    <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-sm btn-warning mt-1">
                        <i class="fas fa-edit"></i> Edit
                    </a>

                    @if($sale->due_amount > 0)
                    <button class="btn btn-sm btn-secondary mt-1" data-bs-toggle="modal"
                        data-bs-target="#payDueModal{{ $sale->id }}">
                        <i class="fas fa-money-check-alt"></i> Pay Due
                    </button>
                    @endif

                    <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure want to delete?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger mt-1">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">No sales found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pay Due Modals -->
    @foreach ($sales as $sale)
        @if($sale->due_amount > 0)
        <div class="modal fade" id="payDueModal{{ $sale->id }}" tabindex="-1" aria-labelledby="payDueModalLabel{{ $sale->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('sales.payDue', $sale->id) }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Pay Due - Invoice #{{ $sale->invoice_no }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                    max="{{ $sale->due_amount }}"
                                    value="{{ $sale->due_amount }}"
                                    step="0.01" required>
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

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $sales->links('pagination::simple-bootstrap-5') }}
    </div>
 </div>
@endsection
