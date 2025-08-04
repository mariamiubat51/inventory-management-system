@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Add Expense</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('expenses.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Date</label>
            <input type="date" name="date" class="form-control" required value="{{ old('date', date('Y-m-d')) }}">
        </div>

        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" class="form-control" required value="{{ old('title') }}">
        </div>

        <div class="mb-3">
            <label>Category</label>
            <select name="category_id" class="form-control" required>
                <option value="">Select Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Amount</label>
            <input type="number" step="0.01" name="amount" class="form-control" required value="{{ old('amount') }}">
        </div>

        <div class="mb-3">
            <label>Account (optional)</label>
            <select name="account_id" class="form-control">
                <option value="">-- Select Account --</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}"
                        @if(old('account_id'))
                            {{ old('account_id') == $account->id ? 'selected' : '' }}
                        @else
                            {{ isset($cashAccountId) && $cashAccountId == $account->id ? 'selected' : '' }}
                        @endif
                    >
                        {{ $account->account_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Note (optional)</label>
            <textarea name="note" class="form-control">{{ old('note') }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Add Expense</button>
        <a href="{{ route('expenses.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
