@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Edit Purchase</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('purchases.update', $purchase->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Supplier</label>
                <select name="supplier_id" class="form-control" required>
                    <option value="">-- Select Supplier --</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ ($purchase->supplier_id == $supplier->id) ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label>Purchase Date</label>
                <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', $purchase->purchase_date->format('Y-m-d')) }}" required>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Account</label>
                <select name="account_id" id="account_id" class="form-control" required>
                    <option value="">-- Select Account --</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" data-balance="{{ $account->total_balance }}"
                            {{ old('account_id', $purchase->account_id) == $account->id ? 'selected' : '' }}>
                            {{ $account->account_name }}
                        </option>
                    @endforeach
                </select>

                <small class="form-text text-muted mt-1 text-success" id="account_balance_text">
                    Available Balance: 0.00
                </small>

                @error('account_id')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <table class="table table-bordered" id="products-table">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Buying Price</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach(old('product_id', $purchase->items->pluck('product_id')) as $index => $productId)
                <tr>
                    <td>
                        <select name="product_id[]" class="form-control product-select" required>
                            <option value="">-- Select Product --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ $product->id == $productId ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="quantity[]" class="form-control qty" min="1" required value="{{ old('quantity')[$index] ?? $purchase->items[$index]->quantity }}">
                    </td>
                    <td>
                        <input type="number" name="buying_price[]" class="form-control price" step="0.01" min="0" required value="{{ old('buying_price')[$index] ?? $purchase->items[$index]->buying_price }}">
                    </td>
                    <td><input type="text" class="form-control subtotal" readonly></td>
                    <td><button type="button" class="btn btn-danger remove-row">❌</button></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <button type="button" id="add-row" class="btn btn-secondary mb-3">➕ Add Product</button>

        <div class="mb-3">
            <strong>Total Items:</strong> <span id="item-count">0</span>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label>Paid Amount</label>
                <input type="number" name="paid_amount" class="form-control" step="0.01" min="0" required value="{{ old('paid_amount', $purchase->paid_amount) }}">
                @error('paid_amount')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label>Due Amount</label>
                <input type="number" name="due_amount" class="form-control" step="0.01" min="0" readonly value="{{ old('due_amount', $purchase->due_amount) }}">
            </div>
            <div class="col-md-4">
                <label>Notes (optional)</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $purchase->notes) }}</textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-3">💾 Update Purchase</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
const productPrices = @json($products->pluck('buying_price', 'id'));

document.addEventListener('DOMContentLoaded', function () {

    function updateProductOptions() {
        const selectedProducts = Array.from(document.querySelectorAll('.product-select'))
            .map(select => select.value)
            .filter(val => val !== '');

        document.querySelectorAll('.product-select').forEach(select => {
            const currentValue = select.value;
            select.querySelectorAll('option').forEach(option => {
                option.disabled = false;
            });
            select.querySelectorAll('option').forEach(option => {
                if (option.value !== '' && option.value !== currentValue && selectedProducts.includes(option.value)) {
                    option.disabled = true;
                }
            });
        });
    }

    document.querySelector('#products-table').addEventListener('change', function (e) {
        if (e.target.classList.contains('product-select')) {
            const row = e.target.closest('tr');
            const productId = e.target.value;

            if (productPrices[productId]) {
                row.querySelector('.price').value = productPrices[productId];
            }

            updateSubtotals();
            updateProductOptions();
        }
    });

    function updateSubtotals() {
        let total = 0;

        document.querySelectorAll('#products-table tbody tr').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty')?.value) || 0;
            const price = parseFloat(row.querySelector('.price')?.value) || 0;
            const subtotal = qty * price;
            row.querySelector('.subtotal').value = subtotal.toFixed(2);
            total += subtotal;
        });

        const paidInput = document.querySelector('input[name="paid_amount"]');
        const dueInput = document.querySelector('input[name="due_amount"]');

        if (!paidInput.dataset.manual) {
            paidInput.value = total.toFixed(2);
            dueInput.value = '0.00';
        } else {
            const paid = parseFloat(paidInput.value) || 0;
            const due = total - paid;
            dueInput.value = due >= 0 ? due.toFixed(2) : '0.00';
        }
    }

    function updateItemCount() {
        const count = document.querySelectorAll('#products-table tbody tr').length;
        document.getElementById('item-count').textContent = count;
    }

    document.querySelector('#add-row').addEventListener('click', function () {
        const tbody = document.querySelector('#products-table tbody');
        const row = tbody.querySelector('tr');
        const clone = row.cloneNode(true);

        clone.querySelector('.product-select').value = '';
        clone.querySelector('.qty').value = 1;
        clone.querySelector('.price').value = '';
        clone.querySelector('.subtotal').value = '';

        tbody.appendChild(clone);
        updateSubtotals();
        updateItemCount();
        updateProductOptions();
    });

    document.querySelector('#products-table').addEventListener('input', function () {
        updateSubtotals();
    });

    document.querySelector('#products-table').addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            const rows = document.querySelectorAll('#products-table tbody tr');
            if (rows.length > 1) {
                e.target.closest('tr').remove();
                updateSubtotals();
                updateItemCount();
                updateProductOptions();
            }
        }
    });

    document.querySelector('input[name="paid_amount"]').addEventListener('input', function () {
        this.dataset.manual = true;
        const paid = parseFloat(this.value) || 0;
        const total = Array.from(document.querySelectorAll('.subtotal'))
            .reduce((acc, input) => acc + (parseFloat(input.value) || 0), 0);
        const due = total - paid;
        document.querySelector('input[name="due_amount"]').value = due >= 0 ? due.toFixed(2) : '0.00';
    });

    updateSubtotals();
    updateItemCount();
    updateProductOptions();

    const accountSelect = document.getElementById('account_id');
    const balanceText = document.getElementById('account_balance_text');
    const paidInput = document.querySelector('input[name="paid_amount"]');

    function updateAccountBalanceDisplay() {
        const selectedOption = accountSelect.options[accountSelect.selectedIndex];
        const balance = selectedOption ? parseFloat(selectedOption.getAttribute('data-balance')) || 0 : 0;
        balanceText.textContent = `Available Balance: ${balance.toFixed(2)}`;

        if (parseFloat(paidInput.value) > balance) {
            paidInput.classList.add('is-invalid');
        } else {
            paidInput.classList.remove('is-invalid');
        }
    }

    accountSelect.addEventListener('change', updateAccountBalanceDisplay);
    paidInput.addEventListener('input', updateAccountBalanceDisplay);

    updateAccountBalanceDisplay();
});
</script>
@endpush
