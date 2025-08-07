@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Point of Sale (POS)</h2>

    <!-- Customer Selection -->
    <div class="mb-3">
        <label for="customer" class="form-label">Select Customer</label>
        <select id="customer" class="form-control">
            <option value="">Walk-in</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- POS Layout: Left (Products), Right (Cart) -->
    <div class="row">
        <!-- Left: Product Grid -->
        <div class="col-md-8">
            <!-- Search Bar -->
            <div class="mb-3">
                <input type="text" id="product-search" class="form-control" placeholder="Search product...">
            </div>

            <!-- Product Grid -->
            <div class="row" id="product-grid">
                @foreach($products as $product)
                    <div class="col-md-3 mb-4 product-card" data-name="{{ strtolower($product->name) }}">
                        <div class="card h-100">
                            <img src="{{ asset('storage/' . $product->image) }}" class="card-img-top" style="height:150px; object-fit:cover;">
                            <div class="card-body">
                                <h5 class="card-title lh-1">{{ $product->name }}</h5>
                                <p class="lh-1">Price: {{ number_format($product->selling_price, 2) }}৳</p>
                                <p class="lh-1">Stock: {{ $product->stock_quantity }}</p>
                                <button class="btn btn-sm btn-primary add-to-cart"
                                        data-id="{{ $product->id }}"
                                        data-name="{{ $product->name }}"
                                        data-price="{{ $product->selling_price }}">
                                    + Add
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Right: Cart -->
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
                    <div class="d-flex align-items-center px-3 py-2" style="background-color: #f8f9fa;">
                        <label for="discount" class="mb-0 me-3">Discount:</label>
                        <input type="number" id="discount" value="0" class="form-control rounded-0 rounded-start" style="width: 120px;" min="0">
                        <select id="discount-type" class="form-select rounded-0 rounded-end" style="width: 120px;">
                            <option value="fixed">৳ Fixed</option>
                            <option value="percent">% Percentage</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex flex-column align-items-end mb-3">
                    <!-- Paid Amount -->
                    <div class="me-4 d-flex align-items-center" style="min-width: 200px;">
                        <label for="paid" class="mb-0 me-2 p-3" style="white-space: nowrap;">Paid Amount:</label>
                        <input type="number" id="paid" class="form-control" min="0" value="0" style="width: 120px;">
                    </div>

                    <!-- Due Amount -->
                    <div class="me-4 d-flex align-items-center" style="min-width: 200px;">
                        <label for="due" class="mb-0 me-2 p-3" style="white-space: nowrap;">Due Amount:</label>
                        <input type="number" id="due" class="form-control" readonly style="width: 120px;">
                    </div>

                    <!-- Payment Account -->
                    <div class="d-flex align-items-center ps-3" style="min-width: 250px;">
                        <label for="account_id" class="mb-0 me-2 px-3" style="white-space: nowrap;">Payment Account:</label>
                        <select name="account_id" id="account_id" class="form-select" style="width: 150px;">
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ $account->account_name == 'Cash' ? 'selected' : '' }}>
                            {{ $account->account_name }}
                            </option>
                        @endforeach
                        </select>
                    </div>
                    <small id="account_balance_info" class="text-success mt-2"></small>
                </div>

                <div class="d-flex justify-content-end mb-2">
                    <strong>Total: <span id="cart-total">0.00</span>৳</strong>
                </div>

                <div class="d-flex justify-content-end mb-2">
                    <strong>Grand Total: <span id="grand-total">0.00</span>৳</strong>
                </div>

                <div class="text-end">
                    <button class="btn btn-success" id="complete-sale">Complete Sale</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let cart = [];
    let userEditedPaid = false;

    document.getElementById('product-search').addEventListener('input', function () {
        const query = this.value.toLowerCase();
        document.querySelectorAll('.product-card').forEach(card => {
            card.style.display = card.dataset.name.includes(query) ? '' : 'none';
        });
    });

    // Render cart table completely (on add/remove)
    function renderCart() {
        let tbody = document.querySelector('#cart-table tbody');
        tbody.innerHTML = '';
        cart.forEach((item, index) => {
            const price = parseFloat(item.price) || 0;
            const qty = parseFloat(item.qty) || 0;
            const subtotal = price * qty;

            tbody.innerHTML += `
                <tr>
                    <td>${item.name}</td>
                    <td><input type="number" class="form-control qty" data-index="${index}" value="${item.qty}" min="1"></td>
                    <td><input type="number" class="form-control price" data-index="${index}" value="${item.price}" min="0" step="0.01"></td>
                    <td>${subtotal.toFixed(2)}৳</td>
                    <td><button class="btn btn-danger btn-sm remove" data-index="${index}">X</button></td>
                </tr>
            `;
        });
        updateTotals();
    }

    // Update total and grand total live
    function updateTotals() {
        let total = 0;
        cart.forEach(item => {
            let price = parseFloat(item.price) || 0;
            let qty = parseFloat(item.qty) || 0;
            total += price * qty;
        });

        let discountValue = parseFloat(document.getElementById('discount').value) || 0;
        let discountType = document.getElementById('discount-type').value;

        let discountAmount = 0;
        if (discountType === 'percent') {
            discountAmount = (discountValue / 100) * total;
        } else {
            discountAmount = discountValue;
        }

        let grandTotal = Math.max(0, total - discountAmount);
        document.getElementById('cart-total').innerText = total.toFixed(2);
        document.getElementById('grand-total').innerText = grandTotal.toFixed(2);

        // Auto-fill paid unless user already typed
        const paidField = document.getElementById('paid');
        if (!userEditedPaid) {
            paidField.value = grandTotal.toFixed(2);
        }

        // Update due based on (possibly updated) paid
        updateDue();
    }

    function updateDue() {
        const grandTotal = parseFloat(document.getElementById('grand-total').innerText) || 0;
        const paid = parseFloat(document.getElementById('paid').value) || 0;
        let due = grandTotal - paid;
        due = due < 0 ? 0 : due;
        document.getElementById('due').value = due.toFixed(2);
    }

    document.getElementById('paid').addEventListener('input', function () {
        userEditedPaid = true;
        updateDue();
    });


    // Add product button click
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-to-cart')) {
            const id = e.target.dataset.id;
            const name = e.target.dataset.name;
            const price = parseFloat(e.target.dataset.price);

            let existing = cart.find(item => item.id == id);
            if (existing) {
                existing.qty++;
            } else {
                cart.push({ id, name, price, qty: 1 });
            }
            renderCart();
        }

        if (e.target.classList.contains('remove')) {
            const index = e.target.dataset.index;
            cart.splice(index, 1);
            renderCart();
        }
    });

    // Quantity input changes
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('qty')) {
            const index = e.target.dataset.index;
            const newQty = parseInt(e.target.value);

            if (newQty > 0) {
                cart[index].qty = newQty;

                const row = document.querySelector(`#cart-table tbody tr:nth-child(${parseInt(index) + 1})`);
                const price = cart[index].price;
                const subtotal = newQty * price;
                row.querySelector('td:nth-child(4)').textContent = subtotal.toFixed(2) + '৳';

                updateTotals();
            }
        }
    });

    // Price input changes
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('price')) {
            const index = e.target.dataset.index;
            const value = e.target.value;

            if (value === '') {
                const row = document.querySelector(`#cart-table tbody tr:nth-child(${parseInt(index) + 1})`);
                row.querySelector('td:nth-child(4)').textContent = '0.00৳';
                cart[index].price = 0;
                updateTotals();
                return;
            }

            const newPrice = parseFloat(value);
            if (!isNaN(newPrice) && newPrice >= 0) {
                cart[index].price = newPrice;

                const row = document.querySelector(`#cart-table tbody tr:nth-child(${parseInt(index) + 1})`);
                const qty = cart[index].qty;
                const subtotal = qty * newPrice;
                row.querySelector('td:nth-child(4)').textContent = subtotal.toFixed(2) + '৳';

                updateTotals();
            }
        }
    });

    // Discount input changes
    document.getElementById('discount').addEventListener('input', updateTotals);
    document.getElementById('discount-type').addEventListener('change', updateTotals);

    document.getElementById('complete-sale').addEventListener('click', function () {
        if (cart.length === 0) {
            alert('Cart is empty. Please add some products.');
            return;
        }

        const customerId = document.getElementById('customer').value || null;
        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const discountType = document.getElementById('discount-type').value;
        const grandTotal = parseFloat(document.getElementById('grand-total').innerText) || 0;
        const paid = parseFloat(document.getElementById('paid').value) || 0;
        const due = parseFloat(document.getElementById('due').value) || 0;
        const accountId = document.getElementById('account_id').value;

        // Prepare data to send to server
        const saleData = {
            customer_id: customerId,
            discount: discount,
            discount_type: discountType,
            grand_total: grandTotal,
            paid_amount: paid,
            due_amount: due,
            account_id: accountId,
            items: cart

        };

        fetch('{{ route('pos.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(saleData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                cart = [];
                renderCart();
                document.getElementById('discount').value = 0;
                document.getElementById('customer').value = ''; // reset customer select
            } else {
                alert('Sale failed: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Sale failed. Check console for details.');
        });
    });

    // show available balance of account
    const accountBalances = @json($accounts->pluck('total_balance', 'id'));

    const accountSelect = document.getElementById('account_id');
    const balanceInfoDiv = document.getElementById('account_balance_info');

    function updateAccountBalanceText() {
        const selectedId = accountSelect.value;
        const balance = accountBalances[selectedId] ?? 0;

        balanceInfoDiv.innerText = `Balance: ৳${parseFloat(balance).toFixed(2)}`;
    }

    accountSelect.addEventListener('change', updateAccountBalanceText);

    // Show balance immediately on page load (default selected)
    updateAccountBalanceText();
</script>
@endsection
