@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Point of Sale (POS)</h2>

    <div class="mb-3">
        <label for="customer" class="form-label">Select Customer</label>
        <select id="customer" name="customer_id" class="form-control">
            <option value="">-- Select Customer --</option>
            <option value="walkin">Walk-in Customer</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
            @endforeach
        </select>
    </div>

    <input type="hidden" id="walkin_name_hidden">
    <input type="hidden" id="walkin_email_hidden">
    <input type="hidden" id="walkin_phone_hidden">
    <input type="hidden" id="walkin_address_hidden">

    <div class="modal fade" id="walkInModal" tabindex="-1" aria-labelledby="walkInModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="walkInModalLabel">Walk-in Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                <label for="walkin_name_modal" class="form-label">Name <span class="text-danger">*</span></label>
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

    <div class="row">
        <div class="col-md-8">
            <div class="mb-3">
                <input type="text" id="product-search" class="form-control" placeholder="Search product...">
            </div>

            <div class="mb-3">
                <label for="barcode" class="form-label">Scan Barcode</label>
                <input type="text" id="barcode" class="form-control" placeholder="Scan barcode here..." autofocus>
            </div>

            <div class="row" id="product-grid">
                @foreach($products as $product)
                    <div class="col-md-3 mb-4 product-card" data-name="{{ strtolower($product->name) }}">
                        <div class="card h-100">
                            <img 
                                src="{{ ($product->image && file_exists(storage_path('app/public/' . $product->image))) 
                                        ? asset('storage/' . $product->image) 
                                        : asset('storage/products/storesyncLogo.png') }}" 
                                class="card-img-top" 
                                style="height:150px; object-fit:cover;" 
                                alt="Product Image"
                            />

                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title lh-1">{{ $product->name }}</h5>
                                <p class="lh-1">Price: {{ number_format($product->selling_price, 2) }}৳</p>

                                @php
                                    $stock_class = 'text-success'; // Default is green
                                    if ($product->stock_quantity <= 0) {
                                        $stock_class = 'text-danger'; // Red for zero stock
                                    } elseif ($product->stock_quantity <= 5) {
                                        $stock_class = 'text-warning'; // Orange for low stock
                                    }
                                @endphp
                                <p class="lh-1 fw-bold {{ $stock_class }}">
                                    Stock: {{ $product->stock_quantity }}
                                </p>

                                <button class="btn btn-sm btn-primary add-to-cart mt-auto"
                                        data-id="{{ $product->id }}"
                                        data-name="{{ $product->name }}"
                                        data-price="{{ $product->selling_price }}"
                                        data-stock="{{ $product->stock_quantity }}" {{-- <-- Important: Added stock data --}}
                                        {{ $product->stock_quantity <= 0 ? 'disabled' : '' }}> {{-- <-- Disables button --}}
                                    + Add
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="col-md-4">
            <div class="border p-3 rounded bg-light shadow">
                <h4 class="mb-3">Cart</h4>
                <table class="table table-bordered" id="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                            <th>X</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
        
                <div class="d-flex justify-content-end mb-2">
                    <strong>Total: <span id="cart-total">0.00</span>৳</strong>
                </div>
                <div class="mb-3">
                    <label for="discount_value" class="form-label">Discount</label>
                    <div class="input-group">
                        <input type="number" id="discount_value" class="form-control" value="0" min="0">
                        <select id="discount_type" class="form-select" style="max-width: 120px;">
                            <option value="fixed" selected>৳ Fixed</option>
                            <option value="percent">% Percent</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end mb-3">
                    <strong>Grand Total: <span id="grand-total">0.00</span>৳</strong>
                </div>

                <hr>
                
                <div class="row">
                    <div class="col-6">
                        <label for="paid_amount" class="form-label">Paid Amount</label>
                        <input type="number" id="paid_amount" class="form-control" min="0" value="0">
                    </div>
                     <div class="col-6">
                        <label for="due_amount" class="form-label">Due Amount</label>
                        <input type="number" id="due_amount" class="form-control" readonly>
                    </div>
                </div>

                <div class="mt-3">
                    <label for="payment_method" class="form-label">Payment Method</label>
                    <select id="payment_method" class="form-select">
                        <option value="Cash">Cash</option>
                        <option value="Card">Card</option>
                        <option value="Mobile Banking">Mobile Banking</option>
                    </select>
                </div>
                
                <div class="mt-3">
                    <label for="account_id" class="form-label">Payment Account</label>
                    <select id="account_id" class="form-select">
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ strtolower($account->account_name) == 'cash' ? 'selected' : '' }}>
                                {{ $account->account_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="text-end mt-4">
                    <button type="button" class="btn btn-success w-100" id="complete-sale">Complete Sale</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast container -->
<div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1050"></div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    // ---- MODAL & CUSTOMER SELECTION LOGIC ----
    const walkInModalEl = document.getElementById('walkInModal');
    const walkInModal = new bootstrap.Modal(walkInModalEl);
    const customerSelect = document.getElementById('customer');

    const barcodeInput = document.getElementById('barcode');
    barcodeInput.focus(); // Focus at page load

    barcodeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const barcode = e.target.value.trim();
            if (barcode !== '') {
                fetch(`/pos/get-product/${barcode}`)
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) addProductToCart(data.product);
                        else alert('Product not found!');
                    })
                    .catch(err => console.error(err));
            }
            e.target.value = '';
            setTimeout(() => barcodeInput.focus(), 50); // Refocus for next scan
        }
    });

    customerSelect.addEventListener('change', function () {
        if (this.value === 'walkin') {
            walkInModal.show();
        }
    });

    document.getElementById('saveWalkinBtn').addEventListener('click', function () {
        const name = document.getElementById('walkin_name_modal').value.trim();
        if (!name) {
            alert('Please enter the walk-in customer name.');
            return;
        }
        document.getElementById('walkin_name_hidden').value = name;
        document.getElementById('walkin_email_hidden').value = document.getElementById('walkin_email_modal').value.trim();
        document.getElementById('walkin_phone_hidden').value = document.getElementById('walkin_phone_modal').value.trim();
        document.getElementById('walkin_address_hidden').value = document.getElementById('walkin_address_modal').value.trim();
        walkInModal.hide();
    });

    // ---- CART & PRODUCT LOGIC ----
    let cart = []; // Cart array
    let userEditedPaid = false;

    // Filter products based on search input
    document.getElementById('product-search').addEventListener('input', function () {
        const query = this.value.toLowerCase();
        document.querySelectorAll('.product-card').forEach(card => {
            card.style.display = card.dataset.name.toLowerCase().includes(query) ? '' : 'none';
        });
    });

    // ---- STEP 2: Add Product to Cart Function ----
    function addProductToCart(product) {
        const existing = cart.find(item => item.id == product.id);
        if (existing) {
            if (existing.qty < product.stock_quantity) {
                existing.qty++;
            } else {
                alert('No more items in stock!');
            }
        } else {
            if (product.stock_quantity > 0) {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: parseFloat(product.selling_price),
                    stock: product.stock_quantity,
                    qty: 1,
                    barcode: product.barcode
                });
            }
        }
        renderCart();

        showToast(`${product.name} added to cart!`, 'info');
    }

    // Render cart table
    function renderCart() {
        const tbody = document.querySelector('#cart-table tbody');
        let cartHtml = '';
        cart.forEach((item, index) => {
            const price = parseFloat(item.price) || 0;
            const qty = parseInt(item.qty) || 0;
            const subtotal = price * qty;
            cartHtml += `
                <tr>
                    <td>${item.name}</td>
                    <td><input type="number" class="form-control form-control-sm qty" data-index="${index}" value="${qty}" min="1"></td>
                    <td><input type="number" class="form-control form-control-sm price" data-index="${index}" value="${price.toFixed(2)}" min="0" step="0.01"></td>
                    <td>${subtotal.toFixed(2)}</td>
                    <td><button class="btn btn-danger btn-sm remove-item" data-id="${item.id}">X</button></td>
                </tr>
            `;
        });
        tbody.innerHTML = cartHtml;
        updateTotals();
    }

    // Update totals and discounts
    function updateTotals() {
        let subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);

        const discountValue = parseFloat(document.getElementById('discount_value').value) || 0;
        const discountType = document.getElementById('discount_type').value;
        let discountAmount = 0;

        if (discountType === 'percent') {
            discountAmount = (subtotal * discountValue) / 100;
        } else {
            discountAmount = discountValue;
        }

        let grandTotal = subtotal - discountAmount;
        document.getElementById('cart-total').innerText = subtotal.toFixed(2);
        document.getElementById('grand-total').innerText = grandTotal.toFixed(2);

        const paidField = document.getElementById('paid_amount');
        if (!userEditedPaid) {
            paidField.value = grandTotal.toFixed(2);
        }
        updateDue();
    }

    // Update due amount
    function updateDue() {
        const grandTotal = parseFloat(document.getElementById('grand-total').innerText) || 0;
        const paid = parseFloat(document.getElementById('paid_amount').value) || 0;
        document.getElementById('due_amount').value = (grandTotal - paid).toFixed(2);
    }

    // ---- HANDLE CART EVENTS ----
    document.addEventListener('click', function(e) {
        // Add product from card
        if (e.target.classList.contains('add-to-cart')) {
            const id = e.target.dataset.id;
            const stock = parseInt(e.target.dataset.stock);
            const name = e.target.dataset.name;
            const price = parseFloat(e.target.dataset.price);
            addProductToCart({ id, name, selling_price: price, stock_quantity: stock, barcode: e.target.dataset.barcode });
        }

        // Remove item
        if (e.target.classList.contains('remove-item')) {
            const idToRemove = e.target.dataset.id;
            cart = cart.filter(item => item.id !== idToRemove);
            renderCart();
        }
    });

    // Update qty and price in cart table
    document.querySelector('#cart-table').addEventListener('input', function(e) {
        const target = e.target;
        if (!target.classList.contains('qty') && !target.classList.contains('price')) return;
        const index = target.dataset.index;
        const item = cart[index];
        if (!item) return;

        if (target.classList.contains('qty')) {
            let newQty = parseInt(target.value) || 1;
            if (newQty > item.stock) {
                alert('Cannot add more. Only ' + item.stock + ' items left in stock.');
                newQty = item.stock;
                target.value = newQty;
            }
            item.qty = newQty;
        }

        if (target.classList.contains('price')) {
            item.price = parseFloat(target.value) || 0;
        }

        const row = target.closest('tr');
        row.querySelector('td:nth-child(4)').textContent = (item.qty * item.price).toFixed(2);
        updateTotals();
    });

    // Discount and paid inputs
    document.getElementById('paid_amount').addEventListener('input', function() { userEditedPaid = true; updateDue(); });
    document.getElementById('discount_value').addEventListener('input', updateTotals);
    document.getElementById('discount_type').addEventListener('change', updateTotals);

    // ---- COMPLETE SALE ----
    document.getElementById('complete-sale').addEventListener('click', function () {
        if (cart.length === 0) { alert('Cart is empty.'); return; }
        const customerId = document.getElementById('customer').value;
        if (!customerId) { alert('Please select a customer.'); return; }

        const saleData = {
            customer_id: customerId,
            items: cart,
            discount_value: parseFloat(document.getElementById('discount_value').value) || 0,
            discount_type: document.getElementById('discount_type').value,
            paid_amount: parseFloat(document.getElementById('paid_amount').value) || 0,
            payment_method: document.getElementById('payment_method').value,
            account_id: document.getElementById('account_id').value,
        };

        if (customerId === 'walkin') {
            saleData.walkin_name = document.getElementById('walkin_name_hidden').value;
            saleData.walkin_email = document.getElementById('walkin_email_hidden').value;
            saleData.walkin_phone = document.getElementById('walkin_phone_hidden').value;
            saleData.walkin_address = document.getElementById('walkin_address_hidden').value;
            if (!saleData.walkin_name) { alert('Walk-in customer name is required.'); return; }
        }

        const completeBtn = this;
        completeBtn.disabled = true;
        completeBtn.innerText = 'Processing...';

        fetch('{{ route('pos.store') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(saleData)
        })
        .then(response => { if (!response.ok) { return response.json().then(err => { throw err; }); } return response.json(); })
        .then(data => { alert(data.message); window.location.reload(); })
        .catch(error => {
            console.error('Error:', error);
            let errorMessage = "An unknown error occurred.";
            if (error.message) { errorMessage = error.message; }
            else if (error.errors) { errorMessage = Object.values(error.errors).flat().join('\n'); }
            alert('Sale Failed:\n' + errorMessage);
        })
        .finally(() => { completeBtn.disabled = false; completeBtn.innerText = 'Complete Sale'; });
    });

    // ---- BARCODE SCANNING ----
    document.getElementById('barcode').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const barcode = e.target.value.trim();
            if (barcode !== '') {
                fetch(`/pos/get-product/${barcode}`)
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            addProductToCart(data.product); // Step 2 applied
                        } else { alert('Product not found!'); }
                    })
                    .catch(err => console.error(err));
            }
            e.target.value = '';
        }
    });
});

function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toast-container');

    // Create toast element
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-bg-${type} border-0 show`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');

    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    // Append toast to container
    toastContainer.appendChild(toastEl);

    // Initialize Bootstrap toast
    const toast = new bootstrap.Toast(toastEl, { delay: 2000 });
    toast.show();

    // Remove toast from DOM after hiding
    toastEl.addEventListener('hidden.bs.toast', () => {
        toastEl.remove();
    });
}

</script>

@endsection