@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create Sale</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('sales.store') }}" method="POST" id="saleForm">
        @csrf

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="sale_date" class="form-label">Sale Date</label>
                <input type="date" name="sale_date" id="sale_date" class="form-control" value="{{ old('sale_date', date('Y-m-d')) }}" required>
            </div>

            <div class="col-md-6">
                <label for="customer_id" class="form-label">Customer</label>
                <select name="customer_id" id="customer_id" class="form-select" required>
                    <option value="" disabled selected>-- Select Customer --</option>
                    <option value="walkin" {{ old('customer_id') == 'walkin' ? 'selected' : '' }}>-- Walk-in Customer --</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
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
                <tr class="sale-item-row">
                    <td>
                        <select name="product_id[]" class="form-select product-select" required>
                            <option value="">-- Select Product --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->selling_price }}">
                                    {{ $product->name }} (Stock: {{ $product->stock_quantity }})
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" name="quantity[]" class="form-control quantity" min="1" value="1" required></td>
                    <td><input type="number" name="selling_price[]" class="form-control selling-price" step="0.01" min="0"></td>
                    <td><input type="number" name="total[]" class="form-control total" step="0.01" readonly></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
                </tr>
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
                        <label>Subtotal</label>
                        <input type="number" id="subtotal" class="form-control" readonly value="0">
                    </div>
                    <div class="mb-3">
                        <label for="discount">Discount</label>
                        <div class="input-group">
                            <input type="number" id="discount" name="discount" class="form-control" step="0.01" min="0" value="{{ old('discount', 0) }}">
                            <select id="discount_type" class="form-select" style="max-width: 130px;">
                                <option value="fixed" selected>৳ Fixed</option>
                                <option value="percent">% Percent</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Grand Total</label>
                        <input type="number" id="grand_total" name="grand_total" class="form-control" readonly value="0">
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3 bg-transparent border-0">
                    <div class="mb-3">
                        <label for="paid_amount">Paid Amount</label>
                        <input type="number" id="paid_amount" name="paid_amount" class="form-control" step="0.01" min="0" value="0" required>
                    </div>
                    <div class="mb-3">
                        <label>Due Amount</label>
                        <input type="number" id="due_amount" name="due_amount" class="form-control" readonly value="0">
                    </div>
                    <div class="mb-3">
                        <label for="payment_method">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-select" required>
                            <option value="Cash" selected>Cash</option>
                            <option value="Bank">Bank</option>
                            <option value="bKash">bKash</option>
                            <option value="Nagad">Nagad</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3 h-100 bg-transparent border-0">
                    <div class="mb-3">
                        <label for="account_id" class="form-label">Account</label>
                        @php
                            $defaultCashAccountId = \App\Models\Account::where('account_type', 'cash')->value('id');
                        @endphp

                        <select name="account_id" id="account_id" class="form-select" required>
                            <option value="">-- Select Account --</option>
                            @foreach (\App\Models\Account::all() as $account)
                                <option value="{{ $account->id }}"
                                    {{ old('account_id', $defaultCashAccountId) == $account->id ? 'selected' : '' }}>
                                    {{ $account->account_name }} (Balance: {{ number_format($account->total_balance, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="note">Note</label>
                        <textarea name="note" id="note" rows="6" class="form-control">{{ old('note') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden inputs to hold Walk-in customer data from modal -->
        <input type="hidden" name="walkin_name" id="walkin_name_hidden">
        <input type="hidden" name="walkin_email" id="walkin_email_hidden">
        <input type="hidden" name="walkin_phone" id="walkin_phone_hidden">
        <input type="hidden" name="walkin_address" id="walkin_address_hidden">

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-success">💾 Save Sale</button>
        </div>
    </form>
</div>

<!-- Walk-in Customer Modal -->
<div class="modal fade" id="walkInModal" tabindex="-1" aria-labelledby="walkInModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="walkInModalLabel">Walk-in Customer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="walkin_name_modal" class="form-label">Name</label>
          <input type="text" id="walkin_name_modal" class="form-control">
        </div>
        <div class="mb-3">
          <label for="walkin_email_modal" class="form-label">Email</label>
          <input type="email" id="walkin_email_modal" class="form-control">
        </div>
        <div class="mb-3">
          <label for="walkin_phone_modal" class="form-label">Phone</label>
          <input type="text" id="walkin_phone_modal" class="form-control">
        </div>
        <div class="mb-3">
          <label for="walkin_address_modal" class="form-label">Address</label>
          <input type="text" id="walkin_address_modal" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="saveWalkinBtn" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Bootstrap modal instance for Walk-in Customer
    const walkInModalEl = document.getElementById('walkInModal');
    const walkInModal = new bootstrap.Modal(walkInModalEl);

    const customerSelect = document.getElementById('customer_id');

    // Show modal when Walk-in selected
    customerSelect.addEventListener('change', function () {
        if (this.value === 'walkin') {
            walkInModal.show();
        }
    });

    // Save Walk-in info from modal inputs to hidden inputs
    document.getElementById('saveWalkinBtn').addEventListener('click', function () {
        document.getElementById('walkin_name_hidden').value = document.getElementById('walkin_name_modal').value.trim();
        document.getElementById('walkin_email_hidden').value = document.getElementById('walkin_email_modal').value.trim();
        document.getElementById('walkin_phone_hidden').value = document.getElementById('walkin_phone_modal').value.trim();
        document.getElementById('walkin_address_hidden').value = document.getElementById('walkin_address_modal').value.trim();
        walkInModal.hide();
    });

    let userChangedPaidAmount = false;

    // 1. Update selling price and total on product change
    document.querySelector('#saleItemsTable').addEventListener('change', function (e) {
        if (e.target.classList.contains('product-select')) {
            let selected = e.target.selectedOptions[0];
            let row = e.target.closest('tr');
            let price = selected && selected.dataset.price ? parseFloat(selected.dataset.price) : 0;

            row.querySelector('.selling-price').value = price;
            let qty = row.querySelector('.quantity').value;
            row.querySelector('.total').value = (qty * price).toFixed(2);

            calculateTotals();
            updateProductDropdowns();
            updateItemCount();
        }
    });

    // 2. Update total on quantity input
    document.querySelector('#saleItemsTable').addEventListener('input', function (e) {
        if (e.target.classList.contains('quantity')) {
            let row = e.target.closest('tr');
            let qty = parseFloat(e.target.value);
            let price = parseFloat(row.querySelector('.selling-price').value);
            row.querySelector('.total').value = (qty * price).toFixed(2);
            calculateTotals();
        }
    });

    // 2b. Update total when selling price is changed
    document.querySelector('#saleItemsTable').addEventListener('input', function (e) {
        if (e.target.classList.contains('selling-price')) {
            let row = e.target.closest('tr');
            let qty = parseFloat(row.querySelector('.quantity').value);
            let price = parseFloat(e.target.value);
            row.querySelector('.total').value = (qty * price).toFixed(2);
            calculateTotals();
        }
    });

    // 3. Add new row
    document.getElementById('addRowBtn').addEventListener('click', function () {
        const tbody = document.querySelector('#saleItemsTable tbody');
        const originalRow = tbody.querySelector('tr.sale-item-row');
        const newRow = originalRow.cloneNode(true);

        // Reset values
        newRow.querySelector('.product-select').selectedIndex = 0;
        newRow.querySelector('.quantity').value = 1;
        newRow.querySelector('.selling-price').value = '';
        newRow.querySelector('.total').value = '0';

        tbody.appendChild(newRow);

        // Trigger change so it calculates correctly
        setTimeout(() => {
            newRow.querySelector('.product-select').dispatchEvent(new Event('change'));
            updateProductDropdowns();
            updateItemCount();
        }, 100);
    });

    // 4. Discount or Paid Amount changes
    document.getElementById('discount').addEventListener('input', calculateTotals);
    document.getElementById('discount_type').addEventListener('change', calculateTotals);

    document.getElementById('paid_amount').addEventListener('input', function () {
        userChangedPaidAmount = true;
        calculateTotals();
    });

    // 5. Totals Calculation
    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.total').forEach(input => {
            subtotal += parseFloat(input.value) || 0;
        });

        document.getElementById('subtotal').value = subtotal.toFixed(2);

        let discount = parseFloat(document.getElementById('discount').value) || 0;
        let discountType = document.getElementById('discount_type').value;
        let discountAmount = 0;

        if (discountType === 'percent') {
            discountAmount = (subtotal * discount) / 100;
        } else {
            discountAmount = discount;
        }

        let grandTotal = subtotal - discountAmount;

        document.getElementById('grand_total').value = grandTotal.toFixed(2);

        let paidInput = document.getElementById('paid_amount');

        if (!userChangedPaidAmount) {
            paidInput.value = grandTotal.toFixed(2);
        }

        let paid = parseFloat(paidInput.value) || 0;

        if (paid > grandTotal) {
            paid = grandTotal;
            paidInput.value = grandTotal.toFixed(2);
        }

        let due = grandTotal - paid;
        document.getElementById('due_amount').value = due.toFixed(2);

        if (grandTotal === 0) {
            userChangedPaidAmount = false;
        }
    }

    // 6. Update dropdowns to disable selected products
    function updateProductDropdowns() {
        const allSelects = document.querySelectorAll('.product-select');
        const selectedValues = Array.from(allSelects)
            .map(select => select.value)
            .filter(val => val !== '');

        allSelects.forEach(select => {
            const currentValue = select.value;
            Array.from(select.options).forEach(option => {
                if (option.value === "") {
                    option.disabled = false;
                } else if (option.value !== currentValue && selectedValues.includes(option.value)) {
                    option.disabled = true;
                } else {
                    option.disabled = false;
                }
            });
        });
    }

    // 7. Remove row
    document.querySelector('#saleItemsTable').addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            let row = e.target.closest('tr');
            let rowCount = document.querySelectorAll('#saleItemsTable tbody tr').length;

            if (rowCount > 1) {
                row.remove();
                calculateTotals();
                updateProductDropdowns();
                updateItemCount();
            } else {
                alert("At least one product row is required.");
            }
        }
    });

    // 8. Trigger change on all product selects on page load
    setTimeout(() => {
        document.querySelectorAll('.product-select').forEach(select => {
            select.dispatchEvent(new Event('change'));
        });
        updateProductDropdowns();
    }, 200);

    // Auto-fill Paid Amount with Grand Total on page load if Paid Amount is empty or zero
    setTimeout(() => {
        let paidInput = document.getElementById('paid_amount');
        let grandTotalInput = document.getElementById('grand_total');

        if (paidInput.value.trim() === '' || parseFloat(paidInput.value) === 0) {
            paidInput.value = grandTotalInput.value;
        }

        calculateTotals();
    }, 300);

    // Update item count
    function updateItemCount() {
        const rowCount = document.querySelectorAll('#saleItemsTable tbody tr').length;
        document.getElementById('item-count').textContent = rowCount;
    }
});
</script>
@endpush
