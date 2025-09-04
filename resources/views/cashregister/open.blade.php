@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Open Cash Register</h2>
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <form action="{{ route('cash-register.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Opening Amount</label>
            <input type="number" name="opening_amount" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary mt-2">Open Register</button>
    </form>
</div>
@endsection
