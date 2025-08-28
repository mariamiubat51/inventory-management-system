<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SellSync - Inventory Management System</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        body {
            background: linear-gradient(135deg, #4c3232ff, #734646ff);
            background-image: url({{ asset('storage/products/1-background-inventory-management.png') }});
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0px 6px 25px rgba(0, 0, 0, 0.15);
        }
        .logo {
            font-size: 3rem;
            color: #b98d25ff;
        }
        .btn-custom {
            border-radius: 30px;
            padding: 10px 30px;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>

    <div class="card p-5 text-center" style="max-width: 450px; width: 100%;">
        <!-- Logo -->
        <div class="mb-3">
            <i class="bi bi-box-seam-fill logo"></i>
        </div>

        <!-- Title -->
        <h1 class="fw-bold">SellSync</h1>
        <p class="text-muted mb-4">Inventory Management System</p>

        <!-- Buttons -->
        <div class="d-grid gap-3">
            <a href="{{ route('login') }}" class="btn btn-warning btn-custom">
                <i class="bi bi-box-arrow-in-right me-2"></i> Login
            </a>
            <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-custom">
                <i class="bi bi-person-plus me-2"></i> Register
            </a>
        </div>

        <!-- Footer -->
        <p class="mt-4 text-muted small">
            © 2025 SellSync | Developed by Lina
        </p>
    </div>

</body>
</html>
