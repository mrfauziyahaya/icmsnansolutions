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

Route::get('/', function () {
    return redirect()->route('login');
});

// Public policy lookup — no auth required
Route::get('/lookup', [LookupController::class, 'index'])->name('lookup');

// Public quote request form — no auth required
Route::get('/quote-request', [QuoteRequestController::class, 'create'])->name('quote.create');
Route::post('/quote-request', [QuoteRequestController::class, 'store'])->name('quote.store');

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
