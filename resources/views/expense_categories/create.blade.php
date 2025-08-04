@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Add Expense Category</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('expense-categories.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
        </div>

        <button type="submit" class="btn btn-success">Add Category</button>
        <a href="{{ route('expense-categories.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
