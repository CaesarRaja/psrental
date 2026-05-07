<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing Page
Route::get('/', function () {
    return view('landing');
})->name('home');

// Auth Routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])->name('login.post');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/register', [App\Http\Controllers\AuthController::class, 'register'])->name('register.post');

Route::get('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// Customer Routes (Protected)
Route::middleware(['auth'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\CustomerController::class, 'dashboard'])->name('dashboard');
    Route::get('/reservasi', [App\Http\Controllers\CustomerController::class, 'reservasi'])->name('reservasi');
    Route::post('/reservasi', [App\Http\Controllers\CustomerController::class, 'storeReservasi'])->name('reservasi.store');
    Route::delete('/reservasi/{id}', [App\Http\Controllers\CustomerController::class, 'cancelReservasi'])->name('reservasi.cancel');
    Route::get('/makanan', [App\Http\Controllers\CustomerController::class, 'makanan'])->name('makanan');
    Route::post('/makanan/order', [App\Http\Controllers\CustomerController::class, 'orderMakanan'])->name('makanan.order');
    Route::delete('/makanan/order/{id}/cancel', [App\Http\Controllers\CustomerController::class, 'cancelFoodOrder'])->name('makanan.order.cancel');
    Route::get('/pembayaran', [App\Http\Controllers\CustomerController::class, 'pembayaran'])->name('pembayaran');
    Route::post('/pembayaran', [App\Http\Controllers\CustomerController::class, 'storePayment'])->name('pembayaran.store');
    Route::get('/reservasi/{id}/invoice', [App\Http\Controllers\CustomerController::class, 'showInvoice'])->name('reservasi.invoice');
    Route::get('/keluhan', [App\Http\Controllers\CustomerController::class, 'keluhan'])->name('keluhan');
    Route::post('/keluhan', [App\Http\Controllers\CustomerController::class, 'storeKeluhan'])->name('keluhan.store');
    Route::post('/billing-extension', [App\Http\Controllers\BillingExtensionController::class, 'store'])->name('billing-extension.store');
});

// Admin Routes (Protected)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');

    // Reservasi Management
    Route::get('/reservasi', [App\Http\Controllers\AdminController::class, 'reservasi'])->name('reservasi');
    Route::post('/reservasi/{id}/approve', [App\Http\Controllers\AdminController::class, 'approveReservasi'])->name('reservasi.approve');
    Route::post('/reservasi/{id}/reject', [App\Http\Controllers\AdminController::class, 'rejectReservasi'])->name('reservasi.reject');
    Route::post('/reservasi/{id}/start', [App\Http\Controllers\AdminController::class, 'startReservasi'])->name('reservasi.start');
    Route::post('/reservasi/{id}/complete', [App\Http\Controllers\AdminController::class, 'completeReservasi'])->name('reservasi.complete');

    // Antrian
    Route::get('/antrian', [App\Http\Controllers\AdminController::class, 'antrian'])->name('antrian');
    Route::post('/antrian/next', [App\Http\Controllers\AdminController::class, 'nextQueue'])->name('antrian.next');
    Route::post('/antrian/call/{id}', [App\Http\Controllers\AdminController::class, 'callQueue'])->name('antrian.call');
    Route::post('/antrian/reset', [App\Http\Controllers\AdminController::class, 'resetQueue'])->name('antrian.reset');
    Route::get('/antrian/current', [App\Http\Controllers\AdminController::class, 'currentQueue'])->name('antrian.current');
    Route::post('/antrian/add', [App\Http\Controllers\AdminController::class, 'addManualQueue'])->name('antrian.add');

    // Pembayaran
    Route::get('/pembayaran', [App\Http\Controllers\AdminController::class, 'pembayaran'])->name('pembayaran');
    Route::post('/pembayaran/{id}/confirm', [App\Http\Controllers\AdminController::class, 'confirmPayment'])->name('pembayaran.confirm');
    Route::post('/pembayaran/{id}/cancel', [App\Http\Controllers\AdminController::class, 'cancelPayment'])->name('pembayaran.cancel');
    Route::get('/pembayaran/{id}/proof', [App\Http\Controllers\AdminController::class, 'downloadProof'])->name('pembayaran.proof');

    // Pengaturan Pembayaran
    Route::get('/payment-settings', [App\Http\Controllers\AdminController::class, 'paymentSettings'])->name('payment.settings');
    Route::post('/payment-settings', [App\Http\Controllers\AdminController::class, 'updatePaymentSettings'])->name('payment.settings.update');

    // Makanan
    Route::get('/makanan', [App\Http\Controllers\AdminController::class, 'makanan'])->name('makanan');
    Route::post('/makanan', [App\Http\Controllers\AdminController::class, 'storeMakanan'])->name('makanan.store');
    Route::post('/makanan/{id}', [App\Http\Controllers\AdminController::class, 'updateMakanan'])->name('makanan.update');
    Route::post('/makanan/{id}/stock', [App\Http\Controllers\AdminController::class, 'updateStock'])->name('makanan.stock');
    Route::delete('/makanan/{id}', [App\Http\Controllers\AdminController::class, 'destroyMakanan'])->name('makanan.destroy');
    Route::post('/makanan/order/{id}/update', [App\Http\Controllers\AdminController::class, 'updateFoodOrder'])->name('makanan.order.update');

    // Keluhan
    Route::get('/keluhan', [App\Http\Controllers\AdminController::class, 'keluhan'])->name('keluhan');
    Route::post('/keluhan/{id}/response', [App\Http\Controllers\AdminController::class, 'responseKeluhan'])->name('keluhan.response');

    // Billing Extensions
    Route::post('/billing-extension/{id}/approve', [App\Http\Controllers\BillingExtensionController::class, 'approve'])->name('billing-extension.approve');
    Route::post('/billing-extension/{id}/reject', [App\Http\Controllers\BillingExtensionController::class, 'reject'])->name('billing-extension.reject');
});