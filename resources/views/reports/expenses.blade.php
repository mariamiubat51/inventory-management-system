@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Expense Report</h2>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card text-white bg-primary shadow">
                <div class="card-body">
                    <h5 class="card-title">Total Expenses</h5>
                    <p class="card-text fs-4 fw-bold">{{ number_format($totalExpenses, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Filter Report</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.expenses') }}" class="mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <label>From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $from_date }}">
                    </div>
                    <div class="col-md-3">
                        <label>To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $to_date }}">
                    </div>
                    <div class="col-md-3 align-self-end">
                        <button type="submit" class="btn btn-primary">Apply Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <!-- Expense Chart -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Expense Trend</h6>
        </div>
        <div class="card-body">
            <canvas id="expenseChart"></canvas>
        </div>
    </div>

    <!-- Expense Table -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Expense Details</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Date</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Account</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expenses as $key => $expense)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $expense->date }}</td>
                                <td>{{ $expense->title }}</td>
                                <td>{{ $expense->category ? $expense->category->name : '' }}</td>
                                <td>{{ $expense->amount }}</td>
                                <td>{{ $expense->account ? $expense->account->account_name : '' }}</td>
                                <td>{{ $expense->note }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('expenseChart').getContext('2d');
    const expenseChart = new Chart(ctx, {
        type: 'line', // you can change to 'bar' if you want
        data: {
            labels: {!! json_encode($expenses->pluck('date')) !!},
            datasets: [{
                label: 'Expense Amount',
                data: {!! json_encode($expenses->pluck('amount')) !!},
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            scales: {
                x: { title: { display: true, text: 'Date' } },
                y: { title: { display: true, text: 'Amount' }, beginAtZero: true }
            }
        }
    });
</script>
@endsection
