<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

// ── Guest Routes ────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
   Route::get('/', function() {
        return redirect()->route('login');
    });
    Route::get('/login',   [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',  [AuthController::class, 'login']);
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// ── Authenticated Routes ────────────────────────────────────────
Route::middleware(['auth', 'check.store'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard — accessible by owner & kasir
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // POS — accessible by kasir & owner
    Route::get('/pos',              [TransactionController::class, 'pos'])->name('pos');
    Route::post('/pos/checkout',    [TransactionController::class, 'store'])->name('pos.checkout');
    Route::get('/pos/search',       [TransactionController::class, 'searchProducts'])->name('pos.search');

    // Transactions history - All User
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');

 // Transaction CRUD — owner only
    Route::middleware('role:owner')->group(function () {
        Route::get('/transactions/{transaction}',       [TransactionController::class, 'show'])->name('transactions.show');
        Route::get('/transactions/{transaction}/edit',  [TransactionController::class, 'edit'])->name('transactions.edit');
        Route::put('/transactions/{transaction}',       [TransactionController::class, 'update'])->name('transactions.update');
        Route::delete('/transactions/{transaction}',    [TransactionController::class, 'destroy'])->name('transactions.destroy');
    });
    
    // Products — owner only
    Route::middleware('role:owner')->group(function () {
        Route::resource('products', ProductController::class);
        Route::get('/products/generate-sku', [ProductController::class, 'generateSku'])->name('products.generate-sku');
    });

    // Reports — owner only
    Route::middleware('role:owner')->group(function () {
        Route::get('/reports',            [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/chart-data', [ReportController::class, 'chartData'])->name('reports.chart');
    });
     // Store settings & kasir management — owner only
    Route::middleware('role:owner')->group(function () {
        Route::get('/settings',             [StoreController::class, 'settings'])->name('store.settings');
        Route::post('/settings',            [StoreController::class, 'updateSettings'])->name('store.settings.update');
        Route::get('/kasir',                [StoreController::class, 'kasir'])->name('store.kasir');
        Route::post('/kasir',               [StoreController::class, 'storeKasir'])->name('store.kasir.store');
        Route::patch('/kasir/{user}/toggle',[StoreController::class, 'toggleKasir'])->name('store.kasir.toggle');
        Route::delete('/kasir/{user}',      [StoreController::class, 'destroyKasir'])->name('store.kasir.destroy');
        Route::get('/categories',           [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories',          [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}',[CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}',[CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    // Profile — all users
    Route::get('/profile',  [StoreController::class, 'profile'])->name('profile');
    Route::post('/profile', [StoreController::class, 'updateProfile'])->name('profile.update');

});
