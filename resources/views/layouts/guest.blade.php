<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100" style="background: linear-gradient(135deg, #4289cbff, #73bdcbff, #b8cfd4ff);">
            <div class=" shadow-lg" style="min-height: 400px;">
                <div class="row g-0" style="height: 100%;">
                    <!-- Left Side: Image -->
                    <div class="col-md-6 d-flex justify-content-center align-items-center border-end">
                        <img src="{{ asset('storage/products/side-image-1.jpg') }}" 
                            style="max-width: 100%; max-height: 100%; object-fit: contain;" 
                            alt="Login Image">
                    </div>
                    <!-- Right Side: Form -->
                    <div class="col-md-6">
                        <div class="card-body p-4">
                            <!-- Logo + App Name side by side -->
                            <div class="d-flex align-items-center justify-content-center mb-4">
                                <img src="{{ asset('storage/storesyncLogo.png') }}" 
                                    class="img-fluid" 
                                    style="height: 50px;" 
                                    alt="StoreSync Logo">
                                <h1 class="ms-2 mb-0 text-white" style="font-size: 2rem;">StoreSync</h1>
                            </div>

                            <!-- Form will go here -->
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
