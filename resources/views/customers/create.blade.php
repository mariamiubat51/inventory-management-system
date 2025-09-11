@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="p-3 col-6 ">
                <h2>Add Customer</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('customers.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
        </div>
        <div class="mb-3">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
        </div>
        <div class="mb-3">
            <label>Address</label>
            <textarea name="address" class="form-control">{{ old('address') }}</textarea>
        </div>
        <div class="mb-3">
            <label>Type</label>
            <select name="type" class="form-control" required>
                <option value="Walk-in">Walk-in</option>
                <option value="Regular" selected>Regular</option>
                <option value="VIP">VIP</option>
            </select>
        </div>
        <button class="btn btn-success">Save</button>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">Back</a>
    </form>
        </div>
    </div>
</div>
@endsection
