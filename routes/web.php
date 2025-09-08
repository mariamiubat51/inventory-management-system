<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductUnitController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\TransactionLogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;



// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

// Products Unit & Category
Route::prefix('products')->name('products.')->group(function () {
    Route::resource('units', ProductUnitController::class)->only(['index','store','update','destroy']);
    Route::resource('categories', ProductCategoryController::class)->only(['index','store','update','destroy']);
});
//Products
Route::resource('products', ProductController::class);



// Suppliers
Route::resource('suppliers', SupplierController::class);

// Accounts
Route::resource('accounts', AccountController::class);
Route::get('/accounts/{id}/ledger', [AccountController::class, 'ledger'])->name('accounts.ledger');

// Purchases
Route::resource('purchases', PurchaseController::class);
Route::post('/purchases/{id}/pay-due', [PurchaseController::class, 'payDue'])->name('purchases.payDue');

// Transaction Logs
Route::resource('transaction_logs', TransactionLogController::class)->only(['index', 'create', 'store']);

// Users
Route::resource('users', UserController::class);

// Customers
Route::resource('customers', CustomerController::class);

// Sales
Route::resource('sales', SaleController::class);
Route::post('/sales/{sale}/pay-due', [SaleController::class, 'payDue'])->name('sales.payDue');

// Expenses
Route::resource('expenses', ExpenseController::class);

// Expense Categories
Route::resource('expense-categories', ExpenseCategoryController::class);

// POS
Route::middleware(['auth'])->group(function() {
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::post('/pos/sale', [POSController::class, 'store'])->name('pos.store');
    Route::get('/pos/get-product/{barcode}', [POSController::class, 'getProductByBarcode']);
    Route::post('/pos/register/open', [CashRegisterController::class, 'openRegister'])->name('cashregister.open');
    Route::post('/pos/register/close', [CashRegisterController::class, 'closeRegister'])->name('cashregister.close');
});


// CashRegister
Route::post('/cashregister/open', [CashRegisterController::class, 'openRegister'])->name('cashregister.open');
Route::post('/cashregister/close', [CashRegisterController::class, 'closeRegister'])->name('cashregister.close');


// Optional: route to view all registers
Route::get('/cashregister', [CashRegisterController::class, 'index'])->name('cashregister.index');
//Route::get('/cash-register/open', [CashRegisterController::class, 'open'])->name('cash-register.open');
//Route::post('/cash-register/store', [CashRegisterController::class, 'store'])->name('cash-register.store');
//Route::post('/cash-register/close/{register}', [CashRegisterController::class, 'close'])->name('cash-register.close');


// stock-movements
Route::middleware(['auth'])->group(function(){
    Route::get('/stock-movements', [StockMovementController::class, 'index'])->name('stock_movements.index');
    Route::get('/stock-movements/create', [StockMovementController::class, 'create'])->name('stock_movements.create');
    Route::post('/stock-movements', [StockMovementController::class, 'store'])->name('stock_movements.store');
});


// Reports
Route::get('/reports/profit', [ReportController::class, 'profit'])->name('reports.profit');
Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
Route::get('/reports/purchases', [ReportController::class, 'purchases'])->name('reports.purchases');
Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
Route::get('/reports/low-stock', [ReportController::class, 'lowStock'])->name('reports.lowStock');
Route::get('/reports/expenses', [ReportController::class, 'expenses'])->name('reports.expenses');


// Settings routes
Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');


// Welcome Page
Route::get('/', function () {
    return view('welcome');
});

// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
