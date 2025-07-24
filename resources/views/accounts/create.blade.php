@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Add New Account</h2>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form --}}
    <form action="{{ route('accounts.store') }}" method="POST">
        @csrf

        {{-- Account Name --}}
        <div class="mb-3">
            <label for="account_name" class="form-label">Account Name</label>
            <input type="text" class="form-control" id="account_name" name="account_name"
                   value="{{ old('account_name') }}" required>
        </div>

        {{-- Account Type --}}
        <div class="mb-3">
            <label for="account_type" class="form-label">Account Type</label>
            <select name="account_type" id="account_type" class="form-control" required>
                <option value="">-- Select Type --</option>
                @foreach($types as $type)
                    <option value="{{ $type }}" {{ (old('account_type', 'cash') == $type) ? 'selected' : '' }}>
                        {{ ucfirst($type) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Initial Balance --}}
        <div class="mb-3">
            <label for="initial_balance" class="form-label">Opening Balance</label>
            <input type="number" step="0.01" class="form-control" id="initial_balance" name="initial_balance"
                   value="{{ old('initial_balance', 0) }}" required>
        </div>

        {{-- Note --}}
        <div class="mb-3">
            <label for="note" class="form-label">Note</label>
            <textarea class="form-control" id="note" name="note" rows="3">{{ old('note') }}</textarea>
        </div>

        {{-- Buttons --}}
        <button type="submit" class="btn btn-success">Save Account</button>
        <a href="{{ route('accounts.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
