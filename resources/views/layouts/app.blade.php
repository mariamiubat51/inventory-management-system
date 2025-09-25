

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>StoreSync: Inventory Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f8f9fc;
      font-family: 'Segoe UI', sans-serif;
    }
    .sidebar {
      height: 100vh;
      background-color: rgba(59, 58, 63, 1);
      color: white;
      position: fixed;
      width: 250px;
      overflow-y: auto;
    }
    .sidebar a {
      color: white;
      text-decoration: none;
      display: block;
      padding: 12px 20px;
    }
    .sidebar a:hover {
      background-color: rgba(43, 41, 41, 1);
    }
    .content {
      margin-left: 250px;
      padding: 2rem;
    }
  </style>
</head>
<body>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<div class="sidebar d-flex flex-column no-print">
    <h4 class="text-center pt-3">
      <img src="{{ asset('storage/storesyncLogo.png') }}" alt="StoreSync" style="height:50px;">
       StoreSync
    </h4>

    <a href="{{ route('dashboard') }}"><i class="fas fa-chart-line me-2"></i>Dashboard</a>

    {{-- Product dropdown --}}
    <a data-bs-toggle="collapse" href="#productMenu" role="button" aria-expanded="false" aria-controls="productMenu">
        <i class="fas fa-boxes me-2"></i>Products <i class="fas fa-chevron-down float-end"></i>
    </a>
    <div class="collapse ps-4" id="productMenu">
        <a href="{{ url('/products') }}" class="d-block my-1"><i class="fas fa-list me-1"></i> Product List</a>
        <a href="{{ url('/products/categories') }}" class="d-block my-1"><i class="fas fa-layer-group me-1"></i> Product Categories</a>
        <a href="{{ url('/products/units') }}" class="d-block my-1"><i class="fas fa-weight-hanging me-1"></i> Product Units</a>
    </div>

    <a href="{{ url('/stock-movements') }}"><i class="fas fa-exchange-alt me-2"></i>Stock Movements</a>
    <a href="{{ url('/customers') }}"><i class="fas fa-user me-2"></i>Customers</a>
    <a href="{{ route('suppliers.index') }}"><i class="fas fa-users me-2"></i>Suppliers</a>
    <a href="{{ url('/purchases') }}"><i class="fas fa-shopping-cart me-2"></i>Purchases</a>
    <a href="{{ url('/sales') }}"><i class="fas fa-solid fa-tag me-2"></i>Sales</a>

    {{-- Expenses dropdown --}}
    <a data-bs-toggle="collapse" href="#expenseMenu" role="button" aria-expanded="false" aria-controls="expenseMenu">
        <i class="fas fa-wallet me-2"></i>Expenses <i class="fas fa-chevron-down float-end"></i>
    </a>
    <div class="collapse ps-4" id="expenseMenu">
        <a href="{{ url('/expenses') }}" class="d-block my-1"><i class="fas fa-list me-1"></i> Expense List</a>
        <a href="{{ url('/expense-categories') }}" class="d-block my-1"><i class="fas fa-tags me-1"></i> Expense Categories</a>
    </div>

    <a href="{{ url('/pos') }}"><i class="fas fa-solid fa-receipt me-2"></i>POS</a>
    <a href="{{ url('/cashregister') }}"><i class="fas fa-cash-register me-2"></i>Cash Register</a>
    
    {{-- Reports dropdown --}}
    <a data-bs-toggle="collapse" href="#reportsMenu" role="button" aria-expanded="false" aria-controls="reportsMenu">
        <i class="fas fa-chart-bar me-2"></i>Reports <i class="fas fa-chevron-down float-end"></i>
    </a>
    <div class="collapse ps-4" id="reportsMenu">
        <a href="{{ url('/reports/profit') }}" class="d-block my-1"><i class="fas fa-dollar-sign me-1"></i> Profit Report</a>
        <a href="{{ url('/reports/inventory') }}" class="d-block my-1"><i class="fas fa-warehouse me-1"></i> Inventory Report</a>
        <a href="{{ url('/reports/sales') }}" class="d-block my-1"><i class="fas fa-solid fa-tag me-1"></i> Sales Report</a>
        <a href="{{ url('/reports/purchases') }}" class="d-block my-1"><i class="fas fa-shopping-cart me-1"></i> Purchase Report</a>
        <a href="{{ url('/reports/expenses') }}" class="d-block my-1"><i class="fas fa-wallet me-1"></i> Expenses Report</a>
    </div>

    <a href="{{ url('/accounts') }}"><i class="fa-solid fa-building-columns me-2"></i>Accounts</a>
    <a href="{{ url('/transaction_logs') }}"><i class="fas fa-file-invoice-dollar me-2"></i>Transaction-Logs</a>
    <a href="{{ url('/users')}}"><i class="fas fa-user me-2"></i>Users</a>
    <a href="{{ url('/settings') }}"><i class="fas fa-cog me-2"></i>Settings</a>
</div>

  <div class="content main-content">
    @yield('content')
  </div>

  @stack('scripts')


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
