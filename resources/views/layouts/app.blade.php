

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>StoreSync Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f8f9fc;
      font-family: 'Segoe UI', sans-serif;
    }
    .sidebar {
      height: 100vh;
      background-color: rgba(95, 58, 58, 1);
      color: white;
      position: fixed;
      width: 250px;
    }
    .sidebar a {
      color: white;
      text-decoration: none;
      display: block;
      padding: 12px 20px;
    }
    .sidebar a:hover {
      background-color: rgba(102, 66, 66, 1);
    }
    .content {
      margin-left: 250px;
      padding: 2rem;
    }
  </style>
</head>
<body>
  <div class="sidebar d-flex flex-column">
    <h4 class="text-center py-3">StoreSync</h4>
    <a href="{{ route('dashboard') }}"><i class="fas fa-chart-line me-2"></i>Dashboard</a>
    <a href="{{ route('products.index') }}"><i class="fas fa-boxes me-2"></i>Products</a>
    <a href="#"><i class="fas fa-exchange-alt me-2"></i>Stock Movements</a>
    <a href="#"><i class="fas fa-user me-2"></i>Customers</a>
    <a href="{{ route('suppliers.index') }}"><i class="fas fa-users me-2"></i>Suppliers</a>
    <a href="{{ url('/purchases') }}"><i class="fas fa-shopping-cart me-2"></i>Purchases</a>
    <a href="#"><i class="fas fa-cash-register me-2"></i>Sales</a>
    <a href="#"><i class="fas fa-wallet me-2"></i>Expense</a>
    <a href="#"><i class="fas fa-cash-register me-2"></i>POS</a>
    <a href="#"><i class="fas fa-chart-bar me-2"></i>Reports</a>
    <a href="{{ url('/accounts') }}"><i class="fa-solid fa-building-columns me-2"></i>Accounts</a>
    <a href="{{ url('/transaction_logs') }}"><i class="fas fa-file-invoice-dollar me-2"></i>Transaction-Logs</a>
    <a href="{{ url('/users')}}"><i class="fas fa-user me-2"></i>Users</a>
    <a href="#"><i class="fas fa-cog me-2"></i>Settings</a>
  </div>

  <div class="content">
    @yield('content')
  </div>

  @stack('scripts')


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
