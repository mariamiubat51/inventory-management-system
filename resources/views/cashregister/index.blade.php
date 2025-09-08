@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Cash Register</h1>

    <table class="table table-bordered">
        <thead>
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
