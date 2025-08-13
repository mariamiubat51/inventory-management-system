
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventory Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    .card-title {
      font-size: 14px;
      color: #6c757d;
    }
    .card-value {
      font-size: 24px;
      font-weight: bold;
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
    <a href="{{ url('/sales') }}"><i class="fas fa-cash-register me-2"></i>Sales</a>

    {{-- Expenses dropdown --}}
    <a data-bs-toggle="collapse" href="#expenseMenu" role="button" aria-expanded="false" aria-controls="expenseMenu">
        <i class="fas fa-wallet me-2"></i>Expenses <i class="fas fa-chevron-down float-end"></i>
    </a>
    <div class="collapse ps-4" id="expenseMenu">
        <a href="{{ url('/expenses') }}" class="d-block my-1"><i class="fas fa-list me-1"></i> Expense List</a>
        <a href="{{ url('/expense-categories') }}" class="d-block my-1"><i class="fas fa-tags me-1"></i> Expense Categories</a>
    </div>

    <a href="{{ url('/pos') }}"><i class="fas fa-cash-register me-2"></i>POS</a>
    <a href="#"><i class="fas fa-chart-bar me-2"></i>Reports</a>
    <a href="{{ url('/accounts') }}"><i class="fa-solid fa-building-columns me-2"></i>Accounts</a>
    <a href="{{ url('/transaction_logs') }}"><i class="fas fa-file-invoice-dollar me-2"></i>Transaction-Logs</a>
    <a href="{{ url('/users')}}"><i class="fas fa-user me-2"></i>Users</a>
    <a href="#"><i class="fas fa-cog me-2"></i>Settings</a>
</div>


  <div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <input type="text" class="form-control w-50" placeholder="Search...">
       <form method="POST" action="{{ route('logout') }}" class="text-end">
            @csrf
            <button type="submit">Logout</button>
        </form>
      <div>
        <i class="fas fa-user-circle fa-2x text-secondary me-2"></i>
        <i class="fas fa-bell fa-2x text-secondary"></i>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <p class="card-title">Total Products</p>
            <div class="card-value">1,250</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <p class="card-title">Total Stock Quantity</p>
            <div class="card-value">34,500</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <p class="card-title">Low Stock</p>
            <div class="card-value">12</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <p class="card-title">Total Suppliers</p>
            <div class="card-value">35</div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">Stock Levels Over Time</div>
          <div class="card-body">
            <canvas id="stockChart"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">Recent Activity</div>
          <div class="card-body">
            <ul class="list-unstyled">
              <li>Added stock bid - 12 hours ago</li>
              <li>Purchased at #1001 - 1 day ago</li>
              <li>Added purchase - 1 day ago</li>
              <li>Sold purchases - 1 day ago</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">Purchase vs Sales Comparison</div>
          <div class="card-body">
            <canvas id="comparisonChart"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">Recent Activity</div>
          <div class="card-body">
            <ul class="list-unstyled">
              <li>Added stock addition - 12 hours ago</li>
              <li>Purchased at sales - 1 day ago</li>
              <li>Sold stock at purchase - 1 day ago</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex gap-3">
      <button class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add New Product</button>
      <button class="btn btn-info"><i class="fas fa-file-alt me-1"></i> Create Purchase Order</button>
      <button class="btn btn-success"><i class="fas fa-dollar-sign me-1"></i> Record Sale</button>
      <button class="btn btn-warning"><i class="fas fa-random me-1"></i> Stock Transfer</button>
    </div>
  </div>

  <script>
    const stockCtx = document.getElementById('stockChart').getContext('2d');
    new Chart(stockCtx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'],
        datasets: [{
          label: 'Stock Levels',
          data: [160, 230, 180, 250, 300, 320, 400, 350],
          borderColor: '#4e73df',
          backgroundColor: 'rgba(78, 115, 223, 0.05)',
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: { beginAtZero: true }
        }
      }
    });

    const comparisonCtx = document.getElementById('comparisonChart').getContext('2d');
    new Chart(comparisonCtx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'],
        datasets: [
          {
            label: 'Purchases',
            data: [100, 120, 90, 140, 180, 200, 220, 210],
            borderColor: '#1cc88a',
            backgroundColor: 'rgba(28, 200, 138, 0.1)',
            tension: 0.4
          },
          {
            label: 'Sales',
            data: [80, 110, 100, 130, 160, 190, 210, 205],
            borderColor: '#36b9cc',
            backgroundColor: 'rgba(54, 185, 204, 0.1)',
            tension: 0.4
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: true }
        },
        scales: {
          y: { beginAtZero: true }
        }
      }
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
