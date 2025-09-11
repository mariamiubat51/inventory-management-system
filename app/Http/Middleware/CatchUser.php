<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatchUser
{
    public function handle(Request $request, Closure $next)
    {
        // Get route name (may be null)
        $routeName = $request->route() ? $request->route()->getName() : null;

        // Get current path as fallback
        $current_path = $request->path();

        // Public routes (accessible without role check)
        $publicRoutes = [
            'login',
            'logout',        // POST logout
            'register',
            'password.request',
            'password.email',
            'password.reset',
            '/'               // homepage
        ];

        // Allow if public route
        if (
            ($routeName && in_array($routeName, $publicRoutes)) ||
            in_array($current_path, $publicRoutes)
        ) {
            return $next($request);
        }

        // Redirect if not logged in
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $role = Auth::user()->role;

        // Admin can access everything
        if ($role === 'admin') {
            return $next($request);
        }

        // Role-based whitelist by route name
        $whitelist = [

            'accountant' => [
                'dashboard',
                'purchases.index','purchases.create','purchases.show','purchases.edit','purchases.update','purchases.destroy','purchases.payDue',
                'sales.index','sales.create','sales.show','sales.edit','sales.update','sales.destroy','sales.payDue',
                'expenses.index','expenses.create','expenses.store','expenses.edit','expenses.update','expenses.destroy',
                'reports.profit','reports.inventory','reports.sales','reports.purchases','reports.expenses',
                'accounts.index','accounts.create','accounts.store','accounts.edit','accounts.update','accounts.destroy','accounts.ledger',
                'transaction_logs.index','transaction_logs.create','transaction_logs.store',
            ],

            'manager' => [
                'dashboard',
                'products.index','products.create','products.store','products.edit','products.update','products.destroy',
                'products.units.index','products.units.store','products.units.update','products.units.destroy',
                'products.categories.index','products.categories.store','products.categories.update','products.categories.destroy',
                'stock_movements.index','stock_movements.create','stock_movements.store',
                'customers.index','customers.create','customers.store','customers.edit','customers.update','customers.destroy',
                'suppliers.index','suppliers.create','suppliers.store','suppliers.edit','suppliers.update','suppliers.destroy',
                'purchases.index','purchases.create','purchases.store','purchases.edit','purchases.update','purchases.destroy','purchases.payDue',
                'sales.index','sales.create','sales.store','sales.edit','sales.update','sales.destroy','sales.payDue',
                'expenses.index','expenses.create','expenses.store','expenses.edit','expenses.update','expenses.destroy',
                'expense-categories.index','expense-categories.create','expense-categories.store','expense-categories.edit','expense-categories.update','expense-categories.destroy',
                'reports.profit','reports.inventory','reports.sales','reports.purchases','reports.expenses',
            ],

            'customer' => [
                'dashboard',
                'sales.index','sales.show',
            ],
        ];

        // Allow if route is in whitelist
        if ($routeName && isset($whitelist[$role]) && in_array($routeName, $whitelist[$role])) {
            return $next($request);
        }

        // Deny access → show 403 page
        return response()->view('errors.access-denied', [], 403);
    }
}
