@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Purchase List</h2>

    <a href="{{ route('purchases.create') }}" class="btn btn-success mb-3" style="background-color: rgba(95, 58, 58, 1);">
        ➕ Add Purchase
    </a>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Invoice No</th>
                <th>Supplier</th>
                <th>Date</th>
                <th>Items</th>
                <th>Total</th>
                <th>Paid</th>
                <th>Due</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($purchases as $purchase)
            <tr>
                <td>{{ $purchase->id }}</td>
                <td>{{ $purchase->invoice_no }}</td>
                <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>
                <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('Y-m-d') }}</td>
                <td>{{ $purchase->items_count }} items</td>
                <td>{{ number_format($purchase->total_amount, 2) }}৳</td>
                <td>{{ number_format($purchase->paid_amount, 2) }}৳</td>
                <td>{{ number_format($purchase->due_amount, 2) }}৳</td>
                <td>
                    @if($purchase->payment_status == 'fully_paid')
                        <span class="badge bg-success">Fully Paid</span>
                    @elseif($purchase->payment_status == 'partially_paid')
                        <span class="badge bg-warning text-dark">Partially Paid</span>
                    @else
                        <span class="badge bg-danger">Unpaid</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-info mt-1">👁 View</a>
                    <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-sm btn-warning mt-1">✏️ Edit</a>
                    <form action="{{ route('purchases.destroy', $purchase->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this purchase?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger mt-1">🗑️ Delete</button>
                    </form>

                    @if($purchase->due_amount > 0)
                        <!-- Pay Due Button -->
                        <button class="btn btn-sm btn-secondary mt-1" data-bs-toggle="modal"
                            data-bs-target="#payDueModal{{ $purchase->id }}">
                            💰 Pay Due
                        </button>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">No purchases found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>


    @foreach ($purchases as $purchase)
    @if($purchase->due_amount > 0)
    <!-- Modal -->
    <div class="modal fade" id="payDueModal{{ $purchase->id }}" tabindex="-1" aria-labelledby="payDueModalLabel{{ $purchase->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('purchases.payDue', $purchase->id) }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pay Due - Invoice #{{ $purchase->invoice_no }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Due Amount: <strong>{{ number_format($purchase->due_amount, 2) }}৳</strong></p>

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
                            <input
                                type="number"
                                name="pay_amount"
                                class="form-control"
                                max="{{ $purchase->due_amount }}"
                                value="{{ $purchase->due_amount }}"
                                step="0.01"
                                required
                                >
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


    {{-- Pagination links --}}
    <div class="d-flex justify-content-center">
        {{ $purchases->links() }}
    </div>
</div>
@endsection
