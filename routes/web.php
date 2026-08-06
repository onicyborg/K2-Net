<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/customers', fn () => view('pages.customers.index'))->name('customers.index');
        Route::get('/packages', fn () => view('pages.packages.index'))->name('packages.index');
        Route::get('/invoices', fn () => view('pages.invoices.index'))->name('invoices.index');
        Route::get('/invoices/{id}', fn ($id) => view('pages.invoices.show', ['id' => $id]))->name('invoices.show');
        Route::get('/verifications', fn () => view('pages.verifications.index'))->name('verifications.index');
        Route::get('/reports', fn () => view('pages.reports.index'))->name('reports.index');
        Route::get('/notifications', fn () => view('pages.notifications.index'))->name('notifications.index');
        Route::get('/configurations', fn () => view('pages.configurations.index'))->name('configurations.index');
    });

    Route::prefix('portal')->name('portal.')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    });
});
