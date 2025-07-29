<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\TransactionLogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SaleController;




Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

    
Route::resource('products', ProductController::class);


Route::resource('suppliers', SupplierController::class);


Route::resource('accounts', AccountController::class);
Route::get('/accounts/{id}/ledger', [AccountController::class, 'ledger'])->name('accounts.ledger');


Route::resource('purchases', PurchaseController::class);
Route::post('/purchases/{id}/pay-due', [PurchaseController::class, 'payDue'])->name('purchases.payDue');


Route::resource('transaction_logs', TransactionLogController::class)->only(['index', 'create', 'store']);


Route::resource('users', UserController::class);


Route::resource('sales', SaleController::class);


Route::get('/', function () {
    return view('welcome');
});

/*Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
