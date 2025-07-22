@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Edit Purchase #{{ $purchase->id }}</h2>

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
                        <option value="{{ $supplier->id }}" 
                            {{ (old('supplier_id', $purchase->supplier_id) == $supplier->id) ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label>Purchase Date</label>
                <input type="date" name="purchase_date" class="form-control" 
                    value="{{ old('purchase_date', $purchase->purchase_date->format('Y-m-d')) }}" required>
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
                @php
                    $oldProducts = old('product_id', $purchase->items->pluck('product_id')->toArray());
                    $oldQtys = old('quantity', $purchase->items->pluck('quantity')->toArray());
                    $oldPrices = old('buying_price', $purchase->items->pluck('buying_price')->toArray());
                @endphp

                @foreach($oldProducts as $index => $productId)
                <tr>
                    <td>
                        <select name="product_id[]" class="form-control product-select" required>
                            <option value="">-- Select Product --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" 
                                    {{ $product->id == $productId ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="quantity[]" class="form-control qty" min="1" required
                            value="{{ $oldQtys[$index] ?? 1 }}">
                    </td>
                    <td>
                        <input type="number" name="buying_price[]" class="form-control price" step="0.01" min="0" required
                            value="{{ $oldPrices[$index] ?? '' }}">
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
                <input type="number" name="paid_amount" class="form-control" step="0.01" min="0" required 
                    value="{{ old('paid_amount', $purchase->paid_amount) }}">
            </div>
            <div class="col-md-4">
                <label>Due Amount</label>
                <input type="number" name="due_amount" class="form-control" step="0.01" min="0" readonly
                    value="{{ old('due_amount', max(0, $purchase->total_amount - $purchase->paid_amount)) }}">
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
            select.querySelectorAll('option').forEach(option => option.disabled = false);

            select.querySelectorAll('option').forEach(option => {
                if (option.value !== '' && option.value !== currentValue && selectedProducts.includes(option.value)) {
                    option.disabled = true;
                }
            });
        });
    }

    // Autofill buying price on product selection
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

    // Update subtotals and paid amount
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

    // Update item count
    function updateItemCount() {
        const count = document.querySelectorAll('#products-table tbody tr').length;
        document.getElementById('item-count').textContent = count;
    }

    // Add new product row
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

    // Recalculate on input
    document.querySelector('#products-table').addEventListener('input', function () {
        updateSubtotals();
    });

    // Remove product row
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

    // Track manual changes in Paid Amount
    document.querySelector('input[name="paid_amount"]').addEventListener('input', function () {
        this.dataset.manual = true;
        const paid = parseFloat(this.value) || 0;
        const total = Array.from(document.querySelectorAll('.subtotal'))
            .reduce((acc, input) => acc + (parseFloat(input.value) || 0), 0);
        const due = total - paid;
        document.querySelector('input[name="due_amount"]').value = due >= 0 ? due.toFixed(2) : '0.00';
    });

    // Initial setup
    updateSubtotals();
    updateItemCount();
    updateProductOptions();

});
</script>
@endpush
