@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Purchase List</h2>

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

  <form method="GET" action="{{ route('purchases.index') }}" class="mb-3">
        <div class="row">
            <!-- Supplier Name -->
            <div class="col-md-3">
                <label for="supplier_name">Supplier Name</label>
                <input type="text" name="supplier_name" class="form-control" value="{{ request('supplier_name') }}" autocomplete="off">
            </div>

            <!-- From Date -->
            <div class="col-md-3">
                <label for="from_date">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>

            <!-- To Date -->
            <div class="col-md-3">
                <label for="to_date">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>

            <!-- Buttons -->
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-info m-1">Search</button>
                <a href="{{ route('purchases.index') }}" class="btn btn-secondary m-1">Reset</a>
            </div>
        </div>
    </form>

    <a href="{{ route('purchases.create') }}" class="btn text-white mb-3" style="background-color: rgba(51, 106, 202, 1);">
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
                    <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-info mt-1"><i class="fas fa-eye"></i> View</a>
                    <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-sm btn-warning mt-1"><i class="fas fa-edit"></i> Edit</a>
                    
                    @if($purchase->due_amount > 0)
                        <!-- Pay Due Button -->
                        <button class="btn btn-sm btn-secondary mt-1" data-bs-toggle="modal"
                            data-bs-target="#payDueModal{{ $purchase->id }}">
                            <i class="fas fa-money-check-alt"></i> Pay Due
                        </button>
                    @endif
                    
                    <form action="{{ route('purchases.destroy', $purchase->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this purchase?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger mt-1"><i class="fas fa-trash"></i> Delete</button>
                    </form>
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
        {{ $purchases->links('pagination::simple-bootstrap-5') }}
    </div>
</div>
@endsection
