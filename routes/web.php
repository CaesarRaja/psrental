<?php

use Illuminate\Support\Facades\Route;
use App\Models\Console;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing Page
Route::get('/', function () {
    $prices = [
        'PS4' => 15000,
        'PS5' => 25000,
        'VR' => 35000,
    ];

    try {
        $dbPrices = Console::select('type', 'price_per_hour')
            ->distinct()
            ->pluck('price_per_hour', 'type')
            ->toArray();

        foreach ($dbPrices as $type => $price) {
            if ($price > 0) {
                $prices[$type] = $price;
            }
        }
    } catch (\Exception $e) {
        // Fallback
    }

    return view('landing', compact('prices'));
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

// Notification Routes (Protected)
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    
    // Live Chat Routes
    Route::get('/chat/messages', [App\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/send', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat/conversations', [App\Http\Controllers\ChatController::class, 'getConversations'])->name('chat.conversations');
    Route::get('/chat/unread-count', [App\Http\Controllers\ChatController::class, 'getUnreadCount'])->name('chat.unreadCount');
    Route::post('/chat/heartbeat', [App\Http\Controllers\ChatController::class, 'heartbeat'])->name('chat.heartbeat');
});

// Customer Routes (Protected)
Route::middleware(['auth', 'customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\CustomerController::class, 'dashboard'])->name('dashboard');
    Route::get('/reservasi', [App\Http\Controllers\Customer\ReservationController::class, 'reservasi'])->name('reservasi');
    Route::post('/reservasi', [App\Http\Controllers\Customer\ReservationController::class, 'storeReservasi'])->name('reservasi.store');
    Route::delete('/reservasi/{id}', [App\Http\Controllers\Customer\ReservationController::class, 'cancelReservasi'])->name('reservasi.cancel');
    Route::delete('/reservasi/{id}/destroy', [App\Http\Controllers\Customer\ReservationController::class, 'destroyReservasi'])->name('reservasi.destroy');
    Route::post('/reservasi/delete-all', [App\Http\Controllers\Customer\ReservationController::class, 'destroyAllReservasi'])->name('reservasi.destroyAll');
    Route::get('/makanan', [App\Http\Controllers\Customer\FoodController::class, 'makanan'])->name('makanan');
    Route::post('/makanan/order', [App\Http\Controllers\Customer\FoodController::class, 'orderMakanan'])->name('makanan.order');
    Route::delete('/makanan/order/{id}/cancel', [App\Http\Controllers\Customer\FoodController::class, 'cancelFoodOrder'])->name('makanan.order.cancel');
    Route::get('/pembayaran', [App\Http\Controllers\Customer\PaymentController::class, 'pembayaran'])->name('pembayaran');
    Route::post('/pembayaran', [App\Http\Controllers\Customer\PaymentController::class, 'storePayment'])->name('pembayaran.store');
    Route::delete('/pembayaran/{id}', [App\Http\Controllers\Customer\PaymentController::class, 'destroyPembayaran'])->name('pembayaran.destroy');
    Route::post('/pembayaran/delete-all', [App\Http\Controllers\Customer\PaymentController::class, 'destroyAllPembayaran'])->name('pembayaran.destroyAll');
    Route::get('/reservasi/{id}/invoice', [App\Http\Controllers\Customer\ReservationController::class, 'showInvoice'])->name('reservasi.invoice');
    Route::get('/keluhan', [App\Http\Controllers\Customer\ComplaintController::class, 'keluhan'])->name('keluhan');
    Route::post('/keluhan', [App\Http\Controllers\Customer\ComplaintController::class, 'storeKeluhan'])->name('keluhan.store');
    Route::delete('/keluhan/{id}', [App\Http\Controllers\Customer\ComplaintController::class, 'destroyKeluhan'])->name('keluhan.destroy');
    Route::post('/keluhan/delete-all', [App\Http\Controllers\Customer\ComplaintController::class, 'destroyAllKeluhan'])->name('keluhan.destroyAll');
    Route::post('/billing-extension', [App\Http\Controllers\BillingExtensionController::class, 'store'])->name('billing-extension.store');

    // Profile
    Route::get('/profile', [App\Http\Controllers\CustomerController::class, 'profile'])->name('profile');
    Route::post('/profile', [App\Http\Controllers\CustomerController::class, 'updateProfile'])->name('profile.update');
});

// Admin Routes (Protected)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');

    // Console Management
    Route::get('/consoles', [App\Http\Controllers\Admin\ConsoleController::class, 'consoles'])->name('consoles');
    Route::post('/consoles/type-price', [App\Http\Controllers\Admin\ConsoleController::class, 'updateConsoleTypePrice'])->name('consoles.updateTypePrice');
    Route::post('/consoles', [App\Http\Controllers\Admin\ConsoleController::class, 'storeConsole'])->name('consoles.store');
    Route::post('/consoles/{id}', [App\Http\Controllers\Admin\ConsoleController::class, 'updateConsole'])->name('consoles.update');
    Route::delete('/consoles/{id}', [App\Http\Controllers\Admin\ConsoleController::class, 'destroyConsole'])->name('consoles.destroy');

    // Reservasi Management
    Route::get('/reservasi', [App\Http\Controllers\Admin\ReservationController::class, 'reservasi'])->name('reservasi');
    Route::post('/reservasi/{id}/approve', [App\Http\Controllers\Admin\ReservationController::class, 'approveReservasi'])->name('reservasi.approve');
    Route::post('/reservasi/{id}/reject', [App\Http\Controllers\Admin\ReservationController::class, 'rejectReservasi'])->name('reservasi.reject');
    Route::post('/reservasi/{id}/start', [App\Http\Controllers\Admin\ReservationController::class, 'startReservasi'])->name('reservasi.start');
    Route::post('/reservasi/{id}/complete', [App\Http\Controllers\Admin\ReservationController::class, 'completeReservasi'])->name('reservasi.complete');
    Route::delete('/reservasi/{id}', [App\Http\Controllers\Admin\ReservationController::class, 'destroyReservasi'])->name('reservasi.destroy');
    Route::post('/reservasi/delete-all', [App\Http\Controllers\Admin\ReservationController::class, 'destroyAllReservasi'])->name('reservasi.destroyAll');

    // Antrian
    Route::get('/antrian', [App\Http\Controllers\Admin\QueueController::class, 'antrian'])->name('antrian');
    Route::post('/antrian/next', [App\Http\Controllers\Admin\QueueController::class, 'nextQueue'])->name('antrian.next');
    Route::post('/antrian/call/{id}', [App\Http\Controllers\Admin\QueueController::class, 'callQueue'])->name('antrian.call');
    Route::post('/antrian/reset', [App\Http\Controllers\Admin\QueueController::class, 'resetQueue'])->name('antrian.reset');
    Route::get('/antrian/current', [App\Http\Controllers\Admin\QueueController::class, 'currentQueue'])->name('antrian.current');
    Route::post('/antrian/add', [App\Http\Controllers\Admin\QueueController::class, 'addManualQueue'])->name('antrian.add');

    // Pembayaran
    Route::get('/pembayaran', [App\Http\Controllers\Admin\PaymentController::class, 'pembayaran'])->name('pembayaran');
    Route::post('/pembayaran/{id}/confirm', [App\Http\Controllers\Admin\PaymentController::class, 'confirmPayment'])->name('pembayaran.confirm');
    Route::post('/pembayaran/{id}/cancel', [App\Http\Controllers\Admin\PaymentController::class, 'cancelPayment'])->name('pembayaran.cancel');
    Route::get('/pembayaran/{id}/proof', [App\Http\Controllers\Admin\PaymentController::class, 'downloadProof'])->name('pembayaran.proof');
    Route::delete('/pembayaran/{id}', [App\Http\Controllers\Admin\PaymentController::class, 'destroyPembayaran'])->name('pembayaran.destroy');
    Route::post('/pembayaran/delete-all', [App\Http\Controllers\Admin\PaymentController::class, 'destroyAllPembayaran'])->name('pembayaran.destroyAll');

    // Pengaturan Pembayaran
    Route::get('/payment-settings', [App\Http\Controllers\Admin\PaymentController::class, 'paymentSettings'])->name('payment.settings');
    Route::post('/payment-settings', [App\Http\Controllers\Admin\PaymentController::class, 'updatePaymentSettings'])->name('payment.settings.update');

    // Makanan
    Route::get('/makanan', [App\Http\Controllers\Admin\FoodController::class, 'makanan'])->name('makanan');
    Route::post('/makanan', [App\Http\Controllers\Admin\FoodController::class, 'storeMakanan'])->name('makanan.store');
    Route::post('/makanan/{id}', [App\Http\Controllers\Admin\FoodController::class, 'updateMakanan'])->name('makanan.update');
    Route::post('/makanan/{id}/stock', [App\Http\Controllers\Admin\FoodController::class, 'updateStock'])->name('makanan.stock');
    Route::delete('/makanan/{id}', [App\Http\Controllers\Admin\FoodController::class, 'destroyMakanan'])->name('makanan.destroy');
    Route::post('/makanan/order/{id}/update', [App\Http\Controllers\Admin\FoodController::class, 'updateFoodOrder'])->name('makanan.order.update');
    Route::delete('/makanan/order/{id}', [App\Http\Controllers\Admin\FoodController::class, 'destroyFoodOrder'])->name('makanan.order.destroy');
    Route::post('/makanan/orders/delete-all', [App\Http\Controllers\Admin\FoodController::class, 'destroyAllFoodOrders'])->name('makanan.orders.destroyAll');

    // Keluhan
    Route::get('/keluhan', [App\Http\Controllers\Admin\ComplaintController::class, 'keluhan'])->name('keluhan');
    Route::post('/keluhan/{id}/response', [App\Http\Controllers\Admin\ComplaintController::class, 'responseKeluhan'])->name('keluhan.response');
    Route::delete('/keluhan/{id}', [App\Http\Controllers\Admin\ComplaintController::class, 'destroyKeluhan'])->name('keluhan.destroy');
    Route::post('/keluhan/delete-all', [App\Http\Controllers\Admin\ComplaintController::class, 'destroyAllKeluhan'])->name('keluhan.destroyAll');

    // Customer Management
    Route::get('/customers', [App\Http\Controllers\Admin\CustomerController::class, 'customers'])->name('customers');
    Route::get('/customers/{id}/edit', [App\Http\Controllers\Admin\CustomerController::class, 'editCustomer'])->name('customers.edit');
    Route::post('/customers/{id}', [App\Http\Controllers\Admin\CustomerController::class, 'updateCustomer'])->name('customers.update');
    Route::delete('/customers/{id}', [App\Http\Controllers\Admin\CustomerController::class, 'destroyCustomer'])->name('customers.destroy');

    // Billing Extensions
    Route::post('/billing-extension/{id}/approve', [App\Http\Controllers\BillingExtensionController::class, 'approve'])->name('billing-extension.approve');
    Route::post('/billing-extension/{id}/reject', [App\Http\Controllers\BillingExtensionController::class, 'reject'])->name('billing-extension.reject');
});