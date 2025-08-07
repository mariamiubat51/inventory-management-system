<div class="row mt-3">
    <!-- Paid Amount -->
    <div class="col-md-4">
        <label for="paid">Paid Amount</label>
        <input type="number" id="paid" name="paid" class="form-control" value="0" min="0">
    </div>

    <!-- Due Amount (auto-calculated) -->
    <div class="col-md-4">
        <label for="due">Due Amount</label>
        <input type="number" id="due" name="due" class="form-control" readonly>
    </div>

    <!-- Account Selection -->
    <div class="col-md-4">
        <label for="account">Payment Account</label>
        <select name="account_id" id="account" class="form-control">
            @foreach($accounts as $account)
                <option value="{{ $account->id }}">{{ $account->name }}</option>
            @endforeach
        </select>
    </div>
</div>
 <script>
    function updateDue() {
        const grandTotal = parseFloat(document.getElementById('grand-total').innerText) || 0;
        const paid = parseFloat(document.getElementById('paid').value) || 0;
        const due = grandTotal - paid;
        document.getElementById('due').value = due >= 0 ? due.toFixed(2) : 0;
    }

    document.getElementById('paid').addEventListener('input', updateDue);
</script>
