@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Account</h2>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('accounts.update', $account->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="account_name" class="form-label">Account Name</label>
            <input type="text" class="form-control" id="account_name" name="account_name" value="{{ old('account_name', $account->account_name) }}" required>
        </div>

        <div class="mb-3">
            <label for="account_type" class="form-label">Account Type</label>
            <select name="account_type" id="account_type" class="form-control" required>
                <option value="">-- Select Type --</option>
                @foreach($types as $type)
                <option value="{{ $type }}" @if(old('account_type', $account->account_type) == $type) selected @endif>{{ $type }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="initial_balance" class="form-label">Opening Balance</label>
            <input type="number" step="0.01" class="form-control" id="initial_balance" name="initial_balance" value="{{ old('initial_balance', $account->initial_balance) }}" required>
        </div>

        <div class="mb-3">
            <label for="note" class="form-label">Note</label>
            <textarea class="form-control" id="note" name="note">{{ old('note', $account->note) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Account</button>
        <a href="{{ route('accounts.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
