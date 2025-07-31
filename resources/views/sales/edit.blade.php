@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Edit Sale - Invoice {{ $sale->invoice_no }}</h2>

    <form action="{{ route('sales.update', $sale->id) }}" method="POST" id="saleForm">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="sale_date" class="form-label">Sale Date</label>
                <input type="date" name="sale_date" id="sale_date" class="form-control" value="{{ $sale->sale_date->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
                <label for="customer_id" class="form-label">Customer</label>
                <select name="customer_id" id="customer_id" class="form-select">
                    <option value="">-- Walk-in (No Customer) --</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" {{ $customer->id == $sale->customer_id ? 'selected' : '' }}>
                            {{ $customer->name }} ({{ $customer->type ?? 'Regular' }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <hr>

        <h5 class="mb-3">Sale Items</h5>
        <table class="table table-bordered" id="saleItemsTable">
            <thead>
                <tr>
                    <th>Product</th>
                    <th width="100px">Qty</th>
                    <th width="150px">Selling Price</th>
                    <th width="150px">Total</th>
                    <th width="50px">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr class="sale-item-row">
                    <td>
                        <select name="product_id[]" class="form-select product-select" required>
                            <option value="">-- Select Product --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->selling_price }}"
                                    {{ $product->id == $item->product_id ? 'selected' : '' }}>
                                    {{ $product->name }} (Stock: {{ $product->stock_quantity }})
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" name="quantity[]" class="form-control quantity" min="1" value="{{ $item->quantity }}" required></td>
                    <td><input type="number" name="selling_price[]" class="form-control selling-price" step="0.01" value="{{ $item->selling_price }}" readonly></td>
                    <td><input type="number" name="total[]" class="form-control total" step="0.01" value="{{ $item->total }}" readonly></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <button type="button" id="addRowBtn" class="btn btn-primary mb-4">+ Add Product</button>

        <div class="mb-3">
            <strong>Total Items:</strong> <span id="item-count">1</span>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card p-3 bg-transparent border-0">
                    <div class="mb-3">
                        <label for="discount">Discount</label>
                        <input type="number" id="discount" name="discount" class="form-control" step="0.01" value="{{ $sale->discount }}">
                    </div>
                    <div class="mb-3">
                        <label>Grand Total</label>
                        <input type="number" id="grand_total" name="grand_total" class="form-control" readonly>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3 bg-transparent border-0">
                    <div class="mb-3">
                        <label for="paid_amount">Paid Amount</label>
                        <input type="number" id="paid_amount" name="paid_amount" class="form-control" step="0.01" value="{{ $sale->paid_amount }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Due Amount</label>
                        <input type="number" id="due_amount" name="due_amount" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="payment_method">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-select" required>
                            <option value="Cash" {{ $sale->payment_method == 'Cash' ? 'selected' : '' }}>Cash</option>
                            <option value="Bank" {{ $sale->payment_method == 'Bank' ? 'selected' : '' }}>Bank</option>
                            <option value="bKash" {{ $sale->payment_method == 'bKash' ? 'selected' : '' }}>bKash</option>
                            <option value="Nagad" {{ $sale->payment_method == 'Nagad' ? 'selected' : '' }}>Nagad</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3 h-100 bg-transparent border-0">
                    <div class="mb-3">
                        <label for="account_id" class="form-label">Account</label>
                        <select name="account_id" id="account_id" class="form-select" required>
                            <option value="">-- Select Account --</option>
                            @foreach (\App\Models\Account::all() as $account)
                                <option value="{{ $account->id }}" {{ $sale->account_id == $account->id ? 'selected' : '' }}>
                                    {{ $account->account_name }} (Balance: {{ number_format($account->total_balance, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="note">Note</label>
                        <textarea name="note" id="note" rows="6" class="form-control">{{ $sale->note }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-success">💾 Update Sale</button>
            <a href="{{ route('sales.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.querySelector('#saleItemsTable tbody');

    function updateItemCount() {
        document.getElementById('item-count').textContent = tableBody.querySelectorAll('tr').length;
    }

    function calculateTotals() {
  let subtotal = 0;
  let discount = parseFloat($('#discount').val()) || 0;
  let grandTotal = 0;

  $('#productRows tr').each(function () {
    const qty = parseFloat($(this).find('.quantity').val()) || 0;
    const price = parseFloat($(this).find('.selling_price').val()) || 0;
    const total = qty * price;
    $(this).find('.total').val(total.toFixed(2));
    subtotal += total;
  });

  $('#subtotal').val(subtotal.toFixed(2));

  grandTotal = subtotal - discount;
  $('#grand_total').val(grandTotal.toFixed(2));

  // 🔄 Set paid amount to grand total ONLY IF paid input is empty or zero
  let currentPaid = parseFloat($('#paid_amount').val()) || 0;
  if (currentPaid === 0) {
    $('#paid_amount').val(grandTotal.toFixed(2));
  }

  const paid = parseFloat($('#paid_amount').val()) || 0;
  const due = grandTotal - paid;
  $('#due_amount').val(due.toFixed(2));
}


    function updateProductDropdowns() {
        const selects = document.querySelectorAll('.product-select');
        const selected = [...selects].map(sel => sel.value).filter(Boolean);

        selects.forEach(select => {
            const current = select.value;
            [...select.options].forEach(option => {
                if (option.value !== "" && option.value !== current && selected.includes(option.value)) {
                    option.disabled = true;
                } else {
                    option.disabled = false;
                }
            });
        });
    }

    document.getElementById('addRowBtn').addEventListener('click', function () {
        const newRow = tableBody.querySelector('tr').cloneNode(true);
        newRow.querySelector('.product-select').selectedIndex = 0;
        newRow.querySelector('.quantity').value = 1;
        newRow.querySelector('.selling-price').value = '';
        newRow.querySelector('.total').value = '0';
        tableBody.appendChild(newRow);
        updateItemCount();
        updateProductDropdowns();
    });

    tableBody.addEventListener('change', function (e) {
        if (e.target.classList.contains('product-select')) {
            const row = e.target.closest('tr');
            const price = parseFloat(e.target.selectedOptions[0].dataset.price) || 0;
            row.querySelector('.selling-price').value = price;
            const qty = parseFloat(row.querySelector('.quantity').value);
            row.querySelector('.total').value = (qty * price).toFixed(2);
            calculateTotals();
            updateProductDropdowns();
            updateItemCount();
        }
    });

    tableBody.addEventListener('input', function (e) {
        if (e.target.classList.contains('quantity')) {
            const row = e.target.closest('tr');
            const qty = parseFloat(e.target.value);
            const price = parseFloat(row.querySelector('.selling-price').value);
            row.querySelector('.total').value = (qty * price).toFixed(2);
            calculateTotals();
        }
    });

    tableBody.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            const row = e.target.closest('tr');
            if (tableBody.querySelectorAll('tr').length > 1) {
                row.remove();
                calculateTotals();
                updateProductDropdowns();
                updateItemCount();
            } else {
                alert('At least one product row is required.');
            }
        }
    });

    document.getElementById('discount').addEventListener('input', calculateTotals);
    document.getElementById('paid_amount').addEventListener('input', calculateTotals);

    // Initial triggers
    document.querySelectorAll('.product-select').forEach(sel => sel.dispatchEvent(new Event('change')));
    setTimeout(() => {
        calculateTotals();
        updateProductDropdowns();
        updateItemCount();
    }, 200);
});
</script>
@endpush
