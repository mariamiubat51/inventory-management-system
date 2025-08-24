@extends('layouts.app')

@section('content')
<h2>Profit Report</h2>
<div class="card p-3">
    <p>Total Sales: ${{ $totalSales }}</p>
    <p>Total Purchases: ${{ $totalPurchases }}</p>
    <p><strong>Profit: ${{ $profit }}</strong></p>
</div>
@endsection
