@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Expense Category</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('expense-categories.update', $expenseCategory->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name', $expenseCategory->name) }}">
        </div>

        <button type="submit" class="btn btn-primary">Update Category</button>
        <a href="{{ route('expense-categories.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
