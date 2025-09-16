@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Cash Register</h1>

    <!-- Error Message -->
    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Date Filter Form -->
    <form method="GET" action="{{ route('cashregister.index') }}" class="row g-3 mb-4">
        <div class="col-md-3">
            <label for="from_date" class="form-label">From Date</label>
            <input type="date" id="from_date" name="from_date" class="form-control"
                   value="{{ request('from_date') }}">
        </div>
        <div class="col-md-3">
            <label for="to_date" class="form-label">To Date</label>
            <input type="date" id="to_date" name="to_date" class="form-control"
                   value="{{ request('to_date') }}">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary me-2">Filter</button>
            <a href="{{ route('cashregister.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <table class="table table-bordered table-hover align-middle table-striped text-center">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Opening Amount</th>
                <th>Closing Amount</th>
                <th>Status</th>
                <th>Total Sales</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cashRegisters as $cash)
            <tr>
                <td>{{ $cash->id }}</td>
                <td>{{ $cash->date }}</td>
                <td>{{ $cash->opening_amount }}</td>
                <td>{{ $cash->closing_amount ?? '0.00' }}</td>
                <td>{{ $cash->status }}</td>
                <td>{{ $cash->total_sales }}</td>
                <td>{{ $cash->notes }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
