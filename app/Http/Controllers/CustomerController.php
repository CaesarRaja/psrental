<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Food;
use App\Models\FoodOrder;
use App\Models\Complaint;
use App\Models\Queue;
use App\Models\Payment;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        $activeReservations = Reservation::where('user_id', $user->id)
            ->where('status', 'active')->count();

        $totalReservations = Reservation::where('user_id', $user->id)->count();

        $totalSpent = Reservation::where('user_id', $user->id)
            ->where('status', 'completed')->sum('total_price');

        $loyaltyPoints = $totalSpent / 1000; // 1 point per 1000 Rupiah

        $recentReservations = Reservation::where('user_id', $user->id)
            ->latest()->take(5)->get();

        // Food orders
        $foodOrders = FoodOrder::where('user_id', $user->id)
            ->latest()->take(5)->get();
        $pendingFoodOrders = FoodOrder::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        // Queue status
        $myQueue = Queue::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->whereIn('status', ['waiting', 'serving'])
            ->first();

        $queueNumber = $myQueue?->queue_number ?? null;
        $currentServing = Queue::whereDate('created_at', today())
            ->where('status', 'serving')
            ->first();
        $currentQueue = $currentServing?->queue_number ?? '00';

        $waitTime = null;
        $queueProgress = 0;
        if ($myQueue) {
            $peopleAhead = Queue::whereDate('created_at', today())
                ->where('status', 'waiting')
                ->where('queue_number', '<', $myQueue->queue_number)
                ->count();
            $waitTime = $peopleAhead > 0
                ? "~" . ($peopleAhead * 5) . " menit"
                : ($myQueue->status === 'serving' ? 'Giliran kamu!' : 'Segera dipanggil');

            $totalWaiting = Queue::whereDate('created_at', today())
                ->whereIn('status', ['waiting', 'serving'])
                ->count();
            $queueProgress = $totalWaiting > 0
                ? round((1 - ($peopleAhead / $totalWaiting)) * 100)
                : 100;
        }

        // Active reservation for billing
        $activeReservation = Reservation::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        $billingTimeRemaining = null;
        if ($activeReservation) {
            if ($activeReservation->started_at) {
                $startTime = strtotime($activeReservation->started_at);
            } else {
                $startTime = strtotime($activeReservation->date . ' ' . $activeReservation->start_time);
            }
            $totalDurationHours = $activeReservation->duration + ($activeReservation->extended_duration / 60);
            $totalDuration = $totalDurationHours * 3600; // in seconds
            $endTime = $startTime + $totalDuration;
            $now = time();
            $billingTimeRemaining = max(0, $endTime - $now);
        }

        return view('customer.dashboard', compact(
            'activeReservations', 'totalReservations', 'totalSpent',
            'loyaltyPoints', 'recentReservations', 'billingTimeRemaining', 'activeReservation',
            'foodOrders', 'pendingFoodOrders',
            'queueNumber', 'currentQueue', 'waitTime', 'queueProgress', 'myQueue'
        ));
    }

    public function reservasi()
    {
        $reservations = Reservation::where('user_id', Auth::id())
            ->latest()->paginate(10);

        $consoles = \App\Models\Console::where('status', 'available')->get();

        return view('customer.reservasi', compact('reservations', 'consoles'));
    }

    public function storeReservasi(Request $request)
    {
        $validated = $request->validate([
            'console_type' => 'required|in:PS4,PS5,VR',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'duration' => 'required|integer|min:1|max:8',
            'total_price' => 'required|numeric',
        ]);

        $reservation = Reservation::create([
            'user_id' => Auth::id(),
            'console_type' => $validated['console_type'],
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'duration' => $validated['duration'],
            'total_price' => $validated['total_price'],
            'status' => 'pending',
        ]);

        NotificationService::notifyAdmins(
            'Reservasi Baru',
            'Reservasi baru dari ' . Auth::user()->name . ' untuk ' . $validated['console_type'] . ' pada ' . $validated['date'] . ' ' . $validated['start_time'],
            route('admin.reservasi'),
            'reservation',
            $reservation->id
        );

        return redirect()->route('customer.reservasi')
            ->with('success', 'Reservasi berhasil dibuat! Menunggu konfirmasi admin.');
    }

    public function cancelReservasi($id)
    {
        $reservation = Reservation::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        $reservation->update(['status' => 'cancelled']);

        return back()->with('success', 'Reservasi berhasil dibatalkan.');
    }

    public function makanan()
    {
        $foods = Food::where('status', 'available')->get();
        $orders = FoodOrder::where('user_id', Auth::id())->latest()->get();

        return view('customer.makanan', compact('foods', 'orders'));
    }

    public function orderMakanan(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|string',
            'total' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        $items = json_decode($validated['items'], true);
        if (!is_array($items) || empty($items)) {
            return back()->with('error', 'Data pesanan tidak valid.');
        }

        // Find active reservation
        $activeReservation = Reservation::where('user_id', Auth::id())
            ->whereIn('status', ['active', 'approved'])
            ->first();

        if (!$activeReservation) {
            return back()->with('error', 'Anda harus memiliki reservasi aktif untuk memesan makanan. Silakan buat reservasi terlebih dahulu.');
        }

        // Validate stock availability
        foreach ($items as $item) {
            $food = Food::find($item['id']);
            if (!$food) {
                return back()->with('error', 'Item makanan tidak ditemukan.');
            }
            if ($food->stock < $item['qty']) {
                return back()->with('error', "Stok {$food->name} tidak mencukupi. Tersedia: {$food->stock}, Diminta: {$item['qty']}");
            }
            if ($food->status !== 'available') {
                return back()->with('error', "{$food->name} saat ini tidak tersedia.");
            }
        }

        // Create order
        $order = FoodOrder::create([
            'user_id' => Auth::id(),
            'reservation_id' => $activeReservation->id,
            'items' => $items,
            'total' => $validated['total'],
            'notes' => $validated['notes'],
            'status' => 'pending',
        ]);

        // Decrease stock
        foreach ($items as $item) {
            $food = Food::find($item['id']);
            if ($food) {
                $food->decrement('stock', $item['qty']);
            }
        }

        NotificationService::notifyAdmins(
            'Pesanan Makanan Baru',
            'Pesanan makanan dari ' . Auth::user()->name . ' senilai Rp ' . number_format($validated['total']),
            route('admin.makanan'),
            'food_order',
            $order->id
        );

        return back()->with('success', 'Pesanan berhasil dibuat! Menunggu konfirmasi admin.');
    }

    public function cancelFoodOrder($id)
    {
        $order = FoodOrder::where('id', $id)
            ->where('user_id', Auth::id())
            ->whereIn('status', ['pending'])
            ->firstOrFail();

        // Restore stock
        foreach ($order->items as $item) {
            $food = Food::find($item['id'] ?? null);
            if ($food) {
                $food->increment('stock', $item['qty'] ?? 1);
            }
        }

        $order->update(['status' => 'cancelled']);

        return back()->with('success', 'Pesanan makanan berhasil dibatalkan.');
    }

    public function pembayaran()
    {
        $user = Auth::user();
        
        // Get reservations that are completed or active (need payment)
        $reservations = Reservation::where('user_id', $user->id)
            ->whereIn('status', ['active', 'completed'])
            ->with('payment')
            ->latest()
            ->paginate(10);
        
        // Get payment history
        $paymentHistory = Payment::where('user_id', $user->id)
            ->with('reservation')
            ->latest()
            ->paginate(10);
        
        $totalSpent = Payment::where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('total');
        
        $pendingPayments = Payment::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        // Get pending food orders for current user
        $pendingFoodOrders = FoodOrder::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        // Payment settings (QRIS + bank)
        $paymentSettings = \App\Models\PaymentSetting::first();

        return view('customer.pembayaran', compact(
            'reservations', 'paymentHistory', 'totalSpent', 'pendingPayments', 'pendingFoodOrders',
            'paymentSettings'
        ));
    }

    public function storePayment(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'method' => 'required|in:cash,transfer,qris',
            'proof_image' => 'nullable|image|max:2048',
        ]);

        if (in_array($validated['method'], ['transfer', 'qris']) && !$request->hasFile('proof_image')) {
            return back()->withErrors(['proof_image' => 'Bukti pembayaran wajib diunggah untuk metode Transfer dan QRIS.'])->withInput();
        }

        $reservation = Reservation::where('id', $validated['reservation_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Calculate food order total for this reservation
        $foodTotal = FoodOrder::where('reservation_id', $reservation->id)
            ->whereIn('status', ['approved', 'delivered'])
            ->sum('total');

        // Check if payment already exists
        $existingPayment = Payment::where('reservation_id', $reservation->id)->first();
        
        $updateData = [
            'user_id' => Auth::id(),
            'reservation_id' => $reservation->id,
            'total' => $reservation->total_price + $foodTotal,
            'method' => $validated['method'],
            'status' => 'pending',
            'payable_type' => 'reservation',
            'payable_id' => $reservation->id,
        ];

        if ($request->hasFile('proof_image')) {
            $path = $request->file('proof_image')->store('payments', 'public');
            $updateData['proof_image'] = $path;
        }

        if ($existingPayment) {
            $existingPayment->update($updateData);
            $paymentId = $existingPayment->id;
        } else {
            $payment = Payment::create($updateData);
            $paymentId = $payment->id;
        }

        NotificationService::notifyAdmins(
            'Bukti Pembayaran Baru',
            'Pembayaran dari ' . Auth::user()->name . ' untuk reservasi #' . $reservation->id . ' senilai Rp ' . number_format($reservation->total_price),
            route('admin.pembayaran'),
            'payment',
            $paymentId
        );

        return back()->with('success', 'Pembayaran berhasil dikirim. Menunggu konfirmasi admin.');
    }

    public function showInvoice($id)
    {
        \Log::info("Show invoice called for reservation ID: {$id}, User ID: " . Auth::id());
        
        $reservation = Reservation::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$reservation) {
            \Log::error("Reservation not found or not owned by user");
            abort(404);
        }

        $foodOrders = FoodOrder::where('reservation_id', $reservation->id)
            ->whereIn('status', ['approved', 'delivered'])
            ->get();

        return view('customer.invoice', compact('reservation', 'foodOrders'));
    }

    public function keluhan()
    {
        $complaints = Complaint::where('user_id', Auth::id())->latest()->get();
        return view('customer.keluhan', compact('complaints'));
    }

    public function storeKeluhan(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|in:console,ruangan,pelayanan,makanan,lainnya',
            'priority' => 'required|in:low,medium,high,urgent',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'attachment' => 'nullable|image|max:2048',
        ]);

        $complaint = Complaint::create([
            'user_id' => Auth::id(),
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'open',
        ]);

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('complaints', 'public');
            $complaint->update(['attachment' => $path]);
        }

        NotificationService::notifyAdmins(
            'Keluhan Baru',
            'Keluhan dari ' . Auth::user()->name . ': ' . $validated['subject'],
            route('admin.keluhan'),
            'complaint',
            $complaint->id
        );

        return back()->with('success', 'Keluhan berhasil dikirim. Admin akan segera merespons.');
    }

    public function destroyReservasi($id)
    {
        $reservation = Reservation::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        if ($reservation->payment && $reservation->payment->proof_image && Storage::disk('public')->exists($reservation->payment->proof_image)) {
            Storage::disk('public')->delete($reservation->payment->proof_image);
        }
        $reservation->payment()->delete();
        $reservation->queue()->delete();
        $reservation->billingExtensions()->delete();
        $reservation->foodOrders()->delete();
        $reservation->delete();

        return back()->with('success', 'Reservasi berhasil dihapus.');
    }

    public function destroyPembayaran($id)
    {
        $payment = Payment::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        if ($payment->proof_image && Storage::disk('public')->exists($payment->proof_image)) {
            Storage::disk('public')->delete($payment->proof_image);
        }
        $payment->delete();

        return back()->with('success', 'Pembayaran berhasil dihapus.');
    }

    public function destroyKeluhan($id)
    {
        $complaint = Complaint::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        if ($complaint->attachment && Storage::disk('public')->exists($complaint->attachment)) {
            Storage::disk('public')->delete($complaint->attachment);
        }
        $complaint->delete();

        return back()->with('success', 'Keluhan berhasil dihapus.');
    }

    public function destroyAllReservasi()
    {
        $userId = Auth::id();
        $reservations = Reservation::where('user_id', $userId)->get();

        foreach ($reservations as $reservation) {
            if ($reservation->payment && $reservation->payment->proof_image && Storage::disk('public')->exists($reservation->payment->proof_image)) {
                Storage::disk('public')->delete($reservation->payment->proof_image);
            }
        }

        Payment::whereIn('reservation_id', $reservations->pluck('id'))->delete();
        Queue::whereIn('reservation_id', $reservations->pluck('id'))->delete();
        \App\Models\BillingExtension::whereIn('reservation_id', $reservations->pluck('id'))->delete();
        FoodOrder::whereIn('reservation_id', $reservations->pluck('id'))->delete();
        Reservation::where('user_id', $userId)->delete();

        return back()->with('success', 'Semua reservasi berhasil dihapus.');
    }

    public function destroyAllPembayaran()
    {
        $userId = Auth::id();
        $payments = Payment::where('user_id', $userId)->whereNotNull('proof_image')->get();
        foreach ($payments as $payment) {
            if (Storage::disk('public')->exists($payment->proof_image)) {
                Storage::disk('public')->delete($payment->proof_image);
            }
        }

        Payment::where('user_id', $userId)->delete();

        return back()->with('success', 'Semua pembayaran berhasil dihapus.');
    }

    public function destroyAllKeluhan()
    {
        $userId = Auth::id();
        $complaints = Complaint::where('user_id', $userId)->whereNotNull('attachment')->get();
        foreach ($complaints as $complaint) {
            if (Storage::disk('public')->exists($complaint->attachment)) {
                Storage::disk('public')->delete($complaint->attachment);
            }
        }

        Complaint::where('user_id', $userId)->delete();

        return back()->with('success', 'Semua keluhan berhasil dihapus.');
    }

    public function profile()
    {
        $user = Auth::user();
        return view('customer.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ];

        if ($request->filled('password')) {
            $rules['current_password'] = 'required|string';
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $validated = $request->validate($rules);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ];

        if ($request->filled('password')) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Password lama tidak cocok.']);
            }
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}