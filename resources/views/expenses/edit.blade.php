@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Expense</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('expenses.update', $expense->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Date</label>
            <input type="date" name="date" class="form-control" required value="{{ old('date', $expense->date) }}">
        </div>

        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" class="form-control" required value="{{ old('title', $expense->title) }}">
        </div>

        <div class="mb-3">
            <label>Category</label>
            <select name="category_id" class="form-control" required>
                <option value="">Select Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ $expense->category_id == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Amount</label>
            <input type="number" step="0.01" name="amount" class="form-control" required value="{{ old('amount', $expense->amount) }}">
        </div>

        <div class="mb-3">
            <label>Account</label>
            <select name="account_id" class="form-control">
                <option value="">-- Select Account --</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" {{ $expense->account_id == $account->id ? 'selected' : '' }}>
                        {{ $account->account_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Note</label>
            <textarea name="note" class="form-control" rows="2">{{ old('note', $expense->note) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Expense</button>
        <a href="{{ route('expenses.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
