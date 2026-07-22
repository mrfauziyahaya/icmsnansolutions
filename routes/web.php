<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LookupController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\WhatsAppNotificationController;
use App\Http\Controllers\QuoteRequestController;
use App\Http\Controllers\PaymentController;

// Public landing page (the site's front door once nansolutions.com.my points here)
Route::view('/', 'landing')->name('landing');

// Public policy lookup — no auth required
Route::get('/lookup', [LookupController::class, 'index'])->name('lookup');

// Public quote request form — no auth required
Route::get('/quote-request', [QuoteRequestController::class, 'create'])->name('quote.create');
Route::post('/quote-request', [QuoteRequestController::class, 'store'])->name('quote.store');
Route::get('/quote-request/success', [QuoteRequestController::class, 'success'])->name('quote.success');

// Public payment checkout — no auth required
Route::get('/pay', [PaymentController::class, 'create'])->name('pay.create');
Route::post('/pay', [PaymentController::class, 'store'])->name('pay.store');
Route::get('/pay/success', [PaymentController::class, 'success'])->name('pay.success');
Route::get('/pay/failed', [PaymentController::class, 'failed'])->name('pay.failed');

// Gateway webhook — CSRF-exempt (see bootstrap/app.php); verified inside the driver
Route::post('/webhooks/payments/{gateway}', [PaymentController::class, 'webhook'])->name('pay.webhook');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Client Management Routes
    Route::get('/clients/expiring', [ClientController::class, 'expiring'])->name('clients.expiring');
    Route::patch('/clients/{client}/renew', [ClientController::class, 'renew'])->name('clients.renew');
    Route::delete('clients/{client}/document', [ClientController::class, 'deleteDocument'])->name('clients.delete-document');
    Route::get('/clients/download', [ClientController::class, 'download'])->name('clients.download');
    Route::resource('clients', ClientController::class);

    // Invoices
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');

    // WhatsApp Notifications Log
    Route::get('/whatsapp-notifications', [WhatsAppNotificationController::class, 'index'])->name('whatsapp.index');

    // Payments
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');

    // Quote Templates (admin comparison quote builder)
    Route::resource('quote-templates', \App\Http\Controllers\QuoteTemplateController::class)
        ->except(['show'])->parameters(['quote-templates' => 'quoteTemplate']);
    Route::get('/quote-templates/{quoteTemplate}', [\App\Http\Controllers\QuoteTemplateController::class, 'show'])->name('quote-templates.show');

    // Quote Requests (Request Sebut Harga)
    Route::get('/quote-requests', [QuoteRequestController::class, 'index'])->name('quote-requests.index');
    Route::get('/quote-requests/{quoteRequest}', [QuoteRequestController::class, 'show'])->name('quote-requests.show');
    Route::patch('/quote-requests/{quoteRequest}/toggle-read', [QuoteRequestController::class, 'toggleRead'])->name('quote-requests.toggle-read');

    // Settings
    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings', [SettingController::class, 'update'])->name('settings.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
