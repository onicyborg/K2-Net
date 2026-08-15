<?php

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\VerificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PortalController;
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

        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{id}', [InvoiceController::class, 'index'])->name('invoices.show');
        Route::get('/verifications', [VerificationController::class, 'index'])->name('verifications.index');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/notifications', fn () => view('pages.notifications.index'))->name('notifications.index');
        Route::get('/configurations', fn () => view('pages.configurations.index'))->name('configurations.index');

        // API routes (session-based auth)
        Route::get('/api/customers/datatable', [CustomerController::class, 'datatable'])->name('api.customers.datatable');
        Route::get('/api/customers/{customer}', [CustomerController::class, 'show'])->name('api.customers.show');
        Route::post('/api/customers', [CustomerController::class, 'store'])->name('api.customers.store');
        Route::put('/api/customers/{customer}', [CustomerController::class, 'update'])->name('api.customers.update');
        Route::delete('/api/customers/{customer}', [CustomerController::class, 'destroy'])->name('api.customers.destroy');

        Route::get('/api/invoices/datatable', [InvoiceController::class, 'datatable'])->name('api.invoices.datatable');
        Route::post('/api/invoices/generate', [InvoiceController::class, 'generate'])->name('api.invoices.generate');
        Route::get('/api/invoices/{invoice}', [InvoiceController::class, 'show'])->name('api.invoices.show');
        Route::put('/api/invoices/{invoice}', [InvoiceController::class, 'update'])->name('api.invoices.update');
        Route::delete('/api/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('api.invoices.destroy');
        Route::get('/api/verifications/datatable', [VerificationController::class, 'datatable'])->name('api.verifications.datatable');
        Route::get('/api/verifications/{submission}', [VerificationController::class, 'show'])->name('api.verifications.show');
        Route::post('/api/verifications/{submission}/approve', [VerificationController::class, 'approve'])->name('api.verifications.approve');
        Route::post('/api/verifications/{submission}/reject', [VerificationController::class, 'reject'])->name('api.verifications.reject');
        Route::get('/api/packages/datatable', [PackageController::class, 'datatable'])->name('api.packages.datatable');
        Route::get('/api/packages/{package}', [PackageController::class, 'show'])->name('api.packages.show');
        Route::post('/api/packages', [PackageController::class, 'store'])->name('api.packages.store');
        Route::put('/api/packages/{package}', [PackageController::class, 'update'])->name('api.packages.update');
        Route::delete('/api/packages/{package}', [PackageController::class, 'destroy'])->name('api.packages.destroy');
        Route::get('/api/reports/{type}', [ReportController::class, 'generate'])->name('api.reports.generate');
    });

    Route::prefix('portal')->name('portal.')->group(function () {
        Route::get('/bayar/{code}', [PortalController::class, 'showPaymentPage'])->name('pay');
        Route::post('/bayar/{code}', [PortalController::class, 'submitPayment'])->name('submit');
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    });
});
