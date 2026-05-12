<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Reservation;
use App\Models\Food;
use App\Models\FoodOrder;
use App\Models\Payment;
use App\Models\Complaint;
use App\Models\Queue;
use App\Models\Console;
use App\Models\BillingExtension;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Auto-reset console status yang tidak punya active reservation
        $activeConsoleTypes = Reservation::where('status', 'active')
            ->distinct('console_type')
            ->pluck('console_type')
            ->toArray();
        
        Console::where('status', 'busy')
            ->whereNotIn('type', $activeConsoleTypes)
            ->update(['status' => 'available']);

        $todayReservations = Reservation::whereDate('created_at', today())->count();
        $activePlaying = Reservation::where('status', 'active')->count();
        $todayRevenue = Reservation::whereDate('created_at', today())
            ->where('status', 'completed')->sum('total_price');
        $queueWaiting = Queue::where('status', 'waiting')->count();
        $totalCustomers = User::where('role', 'customer')->count();
        $newComplaints = Complaint::where('status', 'open')->count();
        $pendingReservations = Reservation::where('status', 'pending')->count();
        $pendingFoodOrders = FoodOrder::where('status', 'pending')->count();

        $consoles = Console::all();
        $availableConsoles = Console::where('status', 'available')->count();
        $busyConsoles = Console::where('status', 'busy')->count();
        $maintenanceConsoles = Console::where('status', 'maintenance')->count();

        $consoleByType = $consoles->groupBy('type')->map(function ($group) {
            return [
                'total' => $group->count(),
                'available' => $group->where('status', 'available')->count(),
                'busy' => $group->where('status', 'busy')->count(),
                'maintenance' => $group->where('status', 'maintenance')->count(),
            ];
        });

        $recentReservations = Reservation::with('customer')->latest()->take(10)->get();

        $weeklyReservations = Reservation::whereBetween('created_at', [now()->subWeek(), now()])->count();
        $newCustomers = User::where('role', 'customer')
            ->whereBetween('created_at', [now()->subWeek(), now()])->count();

        $pendingBillingExtensions = BillingExtension::with('reservation.customer')
            ->where('status', 'pending')->get();

        $activeReservations = Reservation::with('customer')
            ->where('status', 'active')->get();

        return view('admin.dashboard', compact(
            'todayReservations', 'activePlaying', 'todayRevenue',
            'queueWaiting', 'totalCustomers', 'newComplaints',
            'pendingReservations', 'pendingFoodOrders', 'consoles', 'availableConsoles',
            'busyConsoles', 'maintenanceConsoles', 'consoleByType',
            'recentReservations', 'weeklyReservations', 'newCustomers',
            'pendingBillingExtensions', 'activeReservations'
        ));
    }

    public function reservasi(Request $request)
    {
        $query = Reservation::with('customer');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->console) {
            $query->where('console_type', $request->console);
        }
        if ($request->date) {
            $query->whereDate('date', $request->date);
        }

        $reservations = $query->latest()->paginate(15);

        $availableByType = Console::where('status', 'available')
            ->selectRaw('type, COUNT(*) as cnt')
            ->groupBy('type')
            ->pluck('cnt', 'type');

        return view('admin.reservasi', compact('reservations', 'availableByType'));
    }

    public function approveReservasi($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->update(['status' => 'approved']);

        // Add to queue
        Queue::create([
            'user_id' => $reservation->user_id,
            'reservation_id' => $reservation->id,
            'console_type' => $reservation->console_type,
            'status' => 'waiting',
        ]);

        NotificationService::notifyCustomer(
            $reservation->user_id,
            'Reservasi Disetujui',
            'Reservasi kamu untuk ' . $reservation->console_type . ' pada ' . $reservation->date . ' telah disetujui.',
            route('customer.reservasi'),
            'reservation',
            $reservation->id
        );

        return back()->with('success', 'Reservasi disetujui.');
    }

    public function rejectReservasi($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->update(['status' => 'rejected']);

        NotificationService::notifyCustomer(
            $reservation->user_id,
            'Reservasi Ditolak',
            'Reservasi kamu untuk ' . $reservation->console_type . ' pada ' . $reservation->date . ' ditolak.',
            route('customer.reservasi'),
            'reservation',
            $reservation->id
        );

        return back()->with('success', 'Reservasi ditolak.');
    }

    public function startReservasi($id)
    {
        $reservation = Reservation::findOrFail($id);

        if ($reservation->status !== 'approved') {
            return back()->with('error', 'Hanya reservasi berstatus disetujui yang dapat dimulai.');
        }

        return DB::transaction(function () use ($reservation) {
            $console = Console::where('type', $reservation->console_type)
                ->where('status', 'available')
                ->lockForUpdate()
                ->first();

            if (!$console) {
                return back()->with(
                    'console_full',
                    'Mohon maaf, console penuh. Tidak ada unit ' . $reservation->console_type . ' yang tersedia untuk dimulai.'
                );
            }

            $reservation->update([
                'status' => 'active',
                'started_at' => now(),
            ]);

            $console->update(['status' => 'busy']);

            Queue::where('reservation_id', $reservation->id)
                ->where('status', 'waiting')
                ->update(['status' => 'serving']);

            NotificationService::notifyCustomer(
                $reservation->user_id,
                'Sesi Dimulai',
                'Sesi bermain kamu di ' . $reservation->console_type . ' telah dimulai. Selamat bermain!',
                route('customer.dashboard'),
                'reservation',
                $reservation->id
            );

            return back()->with('success', 'Sesi dimulai.');
        });
    }

    public function completeReservasi($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->update(['status' => 'completed']);

        // Calculate food order total
        $foodTotal = FoodOrder::where('reservation_id', $reservation->id)
            ->whereIn('status', ['approved', 'delivered'])
            ->sum('total');

        // Create payment including food orders
        Payment::create([
            'user_id' => $reservation->user_id,
            'reservation_id' => $reservation->id,
            'total' => $reservation->total_price + $foodTotal,
            'method' => 'cash',
            'status' => 'completed',
        ]);

        // Update console status for the reservation type
        Console::where('type', $reservation->console_type)
            ->where('status', 'busy')
            ->first()?->update(['status' => 'available']);

        // Mark related queue as completed
        Queue::where('reservation_id', $reservation->id)
            ->whereIn('status', ['waiting', 'serving'])
            ->update(['status' => 'completed']);

        NotificationService::notifyCustomer(
            $reservation->user_id,
            'Sesi Selesai',
            'Sesi bermain kamu telah selesai. Silakan lakukan pembayaran.',
            route('customer.pembayaran'),
            'reservation',
            $reservation->id
        );

        return back()->with('success', 'Sesi selesai.');
    }

    public function antrian()
    {
        $queues = Queue::with('customer')
            ->where('status', 'waiting')
            ->orderBy('queue_number')
            ->get();

        $currentServing = Queue::with('customer')
            ->where('status', 'serving')
            ->first();

        $todayCompleted = Queue::whereDate('created_at', today())
            ->where('status', 'completed')->count();

        $customers = User::where('role', 'customer')->orderBy('name')->get();

        return view('admin.antrian', compact('queues', 'currentServing', 'todayCompleted', 'customers'));
    }

    public function nextQueue()
    {
        $next = Queue::where('status', 'waiting')->orderBy('queue_number')->first();
        if ($next) {
            Queue::where('status', 'serving')->update(['status' => 'completed']);
            $next->update(['status' => 'serving']);
        }
        return back();
    }

    public function callQueue($id)
    {
        Queue::where('status', 'serving')->update(['status' => 'completed']);
        Queue::findOrFail($id)->update(['status' => 'serving']);
        return back()->with('success', 'Customer dipanggil.');
    }

    public function resetQueue()
    {
        Queue::truncate();
        return back()->with('success', 'Antrian direset.');
    }

    public function currentQueue()
    {
        $queue = Queue::where('status', 'serving')->first();
        return response()->json(['queue_number' => $queue?->queue_number ?? '00']);
    }

    public function addManualQueue(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'console_type' => 'required|in:PS4,PS5,VR',
        ]);

        // Get last queue number for today
        $lastQueue = Queue::whereDate('created_at', today())
            ->orderBy('queue_number', 'desc')
            ->first();
        
        $queueNumber = $lastQueue ? intval($lastQueue->queue_number) + 1 : 1;

        Queue::create([
            'user_id' => $validated['user_id'],
            'reservation_id' => null,
            'console_type' => $validated['console_type'],
            'queue_number' => str_pad($queueNumber, 3, '0', STR_PAD_LEFT),
            'status' => 'waiting',
        ]);

        return back()->with('success', 'Antrian berhasil ditambahkan.');
    }

    public function pembayaran(Request $request)
    {
        $query = Payment::with(['customer', 'reservation.billingExtensions']);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->method) {
            $query->where('method', $request->method);
        }
        if ($request->date) {
            $query->whereDate('created_at', $request->date);
        }

        $payments = $query->latest()->paginate(15);
        $todayRevenue = Payment::whereDate('created_at', today())->sum('total');
        $pendingPayments = Payment::where('status', 'pending')->count();
        $successfulPayments = Payment::where('status', 'completed')->count();

        return view('admin.pembayaran', compact(
            'payments', 'todayRevenue', 'pendingPayments', 'successfulPayments'
        ));
    }

    public function confirmPayment($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->update(['status' => 'completed']);

        // Update reservation status to completed
        if ($payment->reservation) {
            $payment->reservation->update(['status' => 'completed']);
        }

        NotificationService::notifyCustomer(
            $payment->user_id,
            'Pembayaran Dikonfirmasi',
            'Pembayaran kamu senilai Rp ' . number_format($payment->total) . ' telah dikonfirmasi.',
            route('customer.pembayaran'),
            'payment',
            $payment->id
        );

        return response()->json(['success' => true, 'message' => 'Pembayaran dikonfirmasi.']);
    }

    public function cancelPayment(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $payment = Payment::findOrFail($id);
        $payment->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['reason']
        ]);

        NotificationService::notifyCustomer(
            $payment->user_id,
            'Pembayaran Ditolak',
            'Pembayaran kamu ditolak. Alasan: ' . $validated['reason'],
            route('customer.pembayaran'),
            'payment',
            $payment->id
        );

        return response()->json(['success' => true, 'message' => 'Pembayaran ditolak.']);
    }

    public function downloadProof($id)
    {
        $payment = Payment::findOrFail($id);

        if (!$payment->proof_image) {
            abort(404);
        }

        if (!Storage::disk('public')->exists($payment->proof_image)) {
            abort(404, 'File bukti pembayaran tidak ditemukan.');
        }

        return Storage::disk('public')->download($payment->proof_image);
    }

    public function paymentSettings()
    {
        $settings = \App\Models\PaymentSetting::first();
        return view('admin.payment_settings', compact('settings'));
    }

    public function updatePaymentSettings(Request $request)
    {
        $validated = $request->validate([
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'account_holder' => 'nullable|string|max:100',
            'qris_image' => 'nullable|image|max:2048',
        ]);

        $settings = \App\Models\PaymentSetting::firstOrCreate([]);
        $data = [
            'bank_name' => $validated['bank_name'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
            'account_holder' => $validated['account_holder'] ?? null,
        ];

        if ($request->hasFile('qris_image')) {
            $path = $request->file('qris_image')->store('qris', 'public');
            $data['qris_image'] = $path;
        }

        $settings->update($data);

        return back()->with('success', 'Pengaturan pembayaran berhasil diperbarui.');
    }

    public function makanan()
    {
        $foods = Food::latest()->get();
        $foodOrders = FoodOrder::with('customer')->latest()->get();
        return view('admin.makanan', compact('foods', 'foodOrders'));
    }

    public function storeMakanan(Request $request)
    {
        $validated = $request->validate([
            'photo' => 'nullable|image|max:2048',
            'name' => 'required|string|max:255',
            'category' => 'required|in:Makanan,Minuman,Snack',
            'price' => 'required|numeric',
            'stock' => 'required|integer|min:0',
            'status' => 'required|in:available,unavailable',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('foods', 'public');
        }

        Food::create($validated);
        return back()->with('success', 'Makanan berhasil ditambahkan.');
    }

    public function updateMakanan(Request $request, $id)
    {
        $food = Food::findOrFail($id);

        $validated = $request->validate([
            'photo' => 'nullable|image|max:2048',
            'name' => 'required|string|max:255',
            'category' => 'required|in:Makanan,Minuman,Snack',
            'price' => 'required|numeric',
            'stock' => 'required|integer|min:0',
            'status' => 'required|in:available,unavailable',
        ]);

        if ($request->hasFile('photo')) {
            if ($food->photo && Storage::disk('public')->exists($food->photo)) {
                Storage::disk('public')->delete($food->photo);
            }
            $validated['photo'] = $request->file('photo')->store('foods', 'public');
        }

        $food->update($validated);
        return back()->with('success', 'Makanan berhasil diperbarui.');
    }

    public function updateStock(Request $request, $id)
    {
        $food = Food::findOrFail($id);
        $change = $request->input('change', 1);
        $food->increment('stock', $change);

        return response()->json(['success' => true]);
    }

    public function destroyMakanan($id)
    {
        $food = Food::findOrFail($id);
        if ($food->photo && Storage::disk('public')->exists($food->photo)) {
            Storage::disk('public')->delete($food->photo);
        }
        $food->delete();
        return back()->with('success', 'Makanan berhasil dihapus.');
    }

    public function updateFoodOrder(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,preparing,delivered,rejected,cancelled',
        ]);

        $order = FoodOrder::findOrFail($id);
        $oldStatus = $order->status;
        $newStatus = $validated['status'];

        // If rejecting a pending/approved order, restore stock
        if (in_array($newStatus, ['rejected', 'cancelled']) && !in_array($oldStatus, ['rejected', 'cancelled', 'delivered'])) {
            foreach ($order->items as $item) {
                $food = Food::find($item['id'] ?? null);
                if ($food) {
                    $food->increment('stock', $item['qty'] ?? 1);
                }
            }
        }

        $order->update(['status' => $newStatus]);

        $statusLabels = [
            'approved' => 'diterima',
            'preparing' => 'sedang diproses',
            'delivered' => 'selesai dan diantar',
            'rejected' => 'ditolak',
            'cancelled' => 'dibatalkan',
        ];
        $label = $statusLabels[$newStatus] ?? $newStatus;

        NotificationService::notifyCustomer(
            $order->user_id,
            'Status Pesanan Diperbarui',
            'Pesanan makanan kamu telah ' . $label . '.',
            route('customer.makanan'),
            'food_order',
            $order->id
        );

        return back()->with('success', 'Status pesanan diperbarui.');
    }

    public function keluhan()
    {
        $complaints = Complaint::with('customer')->latest()->get();

        $total = $complaints->count();
        $openComplaints = $complaints->where('status', 'open')->count();
        $progressComplaints = $complaints->where('status', 'in_progress')->count();
        $resolvedComplaints = $complaints->where('status', 'resolved')->count();

        $openPercentage = $total > 0 ? ($openComplaints / $total * 100) : 0;
        $progressPercentage = $total > 0 ? ($progressComplaints / $total * 100) : 0;
        $resolvedPercentage = $total > 0 ? ($resolvedComplaints / $total * 100) : 0;

        return view('admin.keluhan', compact(
            'complaints', 'openComplaints', 'progressComplaints', 'resolvedComplaints',
            'openPercentage', 'progressPercentage', 'resolvedPercentage'
        ));
    }

    public function responseKeluhan(Request $request, $id)
    {
        $validated = $request->validate([
            'response' => 'required|string',
            'status' => 'required|in:resolved,in_progress,closed',
        ]);

        $complaint = Complaint::findOrFail($id);
        $complaint->update([
            'response' => $validated['response'],
            'status' => $validated['status'],
        ]);

        NotificationService::notifyCustomer(
            $complaint->user_id,
            'Keluhan Direspons',
            'Admin telah merespons keluhan kamu: ' . $complaint->subject,
            route('customer.keluhan'),
            'complaint',
            $complaint->id
        );

        return back()->with('success', 'Respon berhasil dikirim.');
    }

    // Customer Management
    public function customers(Request $request)
    {
        $search = $request->input('search');
        $query = User::where('role', 'customer');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $customers = $query->latest()->paginate(15);
        return view('admin.customers.index', compact('customers', 'search'));
    }

    public function editCustomer($id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);
        return view('admin.customers.edit', compact('customer'));
    }

    public function updateCustomer(Request $request, $id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ];

        if ($request->filled('password')) {
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
            $updateData['password'] = Hash::make($validated['password']);
        }

        $customer->update($updateData);

        return redirect()->route('admin.customers')
            ->with('success', 'Data customer berhasil diperbarui.');
    }

    public function destroyCustomer($id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);

        // Delete related data
        $customer->reservations()->delete();
        $customer->foodOrders()->delete();
        $customer->complaints()->delete();
        $customer->payments()->delete();

        $customer->delete();

        return redirect()->route('admin.customers')
            ->with('success', 'Akun customer berhasil dihapus.');
    }

    public function destroyReservasi($id)
    {
        $reservation = Reservation::findOrFail($id);

        // Delete related data
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
        $payment = Payment::findOrFail($id);

        if ($payment->proof_image && Storage::disk('public')->exists($payment->proof_image)) {
            Storage::disk('public')->delete($payment->proof_image);
        }
        $payment->delete();

        return back()->with('success', 'Pembayaran berhasil dihapus.');
    }

    public function destroyFoodOrder($id)
    {
        $order = FoodOrder::findOrFail($id);

        // Restore stock if order was not rejected/cancelled/delivered
        if (!in_array($order->status, ['rejected', 'cancelled', 'delivered'])) {
            foreach ($order->items as $item) {
                $food = Food::find($item['id'] ?? null);
                if ($food) {
                    $food->increment('stock', $item['qty'] ?? 1);
                }
            }
        }

        $order->delete();

        return back()->with('success', 'Pesanan makanan berhasil dihapus.');
    }

    public function destroyKeluhan($id)
    {
        $complaint = Complaint::findOrFail($id);

        if ($complaint->attachment && Storage::disk('public')->exists($complaint->attachment)) {
            Storage::disk('public')->delete($complaint->attachment);
        }
        $complaint->delete();

        return back()->with('success', 'Keluhan berhasil dihapus.');
    }

    public function destroyAllReservasi()
    {
        $reservations = Reservation::all();

        foreach ($reservations as $reservation) {
            if ($reservation->payment && $reservation->payment->proof_image && Storage::disk('public')->exists($reservation->payment->proof_image)) {
                Storage::disk('public')->delete($reservation->payment->proof_image);
            }
        }

        Payment::whereIn('reservation_id', $reservations->pluck('id'))->delete();
        Queue::whereIn('reservation_id', $reservations->pluck('id'))->delete();
        \App\Models\BillingExtension::whereIn('reservation_id', $reservations->pluck('id'))->delete();
        FoodOrder::whereIn('reservation_id', $reservations->pluck('id'))->delete();
        Reservation::query()->delete();

        return back()->with('success', 'Semua reservasi berhasil dihapus.');
    }

    public function destroyAllPembayaran()
    {
        $payments = Payment::whereNotNull('proof_image')->get();
        foreach ($payments as $payment) {
            if (Storage::disk('public')->exists($payment->proof_image)) {
                Storage::disk('public')->delete($payment->proof_image);
            }
        }

        Payment::query()->delete();

        return back()->with('success', 'Semua pembayaran berhasil dihapus.');
    }

    public function destroyAllFoodOrders()
    {
        $orders = FoodOrder::whereNotIn('status', ['rejected', 'cancelled', 'delivered'])->get();
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $food = Food::find($item['id'] ?? null);
                if ($food) {
                    $food->increment('stock', $item['qty'] ?? 1);
                }
            }
        }

        FoodOrder::query()->delete();

        return back()->with('success', 'Semua pesanan makanan berhasil dihapus.');
    }

    public function destroyAllKeluhan()
    {
        $complaints = Complaint::whereNotNull('attachment')->get();
        foreach ($complaints as $complaint) {
            if (Storage::disk('public')->exists($complaint->attachment)) {
                Storage::disk('public')->delete($complaint->attachment);
            }
        }

        Complaint::query()->delete();

        return back()->with('success', 'Semua keluhan berhasil dihapus.');
    }

    // Console Management
    public function consoles()
    {
        $consoles = Console::latest()->get();

        $totalConsoles = $consoles->count();
        $availableConsoles = $consoles->where('status', 'available')->count();
        $busyConsoles = $consoles->where('status', 'busy')->count();
        $maintenanceConsoles = $consoles->where('status', 'maintenance')->count();

        $consoleByType = $consoles->groupBy('type')->map(function ($group) {
            return [
                'total' => $group->count(),
                'available' => $group->where('status', 'available')->count(),
                'busy' => $group->where('status', 'busy')->count(),
                'maintenance' => $group->where('status', 'maintenance')->count(),
            ];
        });

        $typePrices = Console::select('type', 'price_per_hour')
            ->distinct()
            ->pluck('price_per_hour', 'type')
            ->toArray();

        return view('admin.consoles', compact(
            'consoles', 'totalConsoles', 'availableConsoles', 'busyConsoles', 'maintenanceConsoles', 'consoleByType', 'typePrices'
        ));
    }

    public function storeConsole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:consoles,name',
            'type' => 'required|in:PS4,PS5,VR',
            'status' => 'required|in:available,busy,maintenance',
        ]);

        $existingPrice = Console::where('type', $validated['type'])->value('price_per_hour');
        $validated['price_per_hour'] = $existingPrice ?? 0;

        Console::create($validated);
        return back()->with('success', 'Console berhasil ditambahkan.');
    }

    public function updateConsole(Request $request, $id)
    {
        $console = Console::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:consoles,name,' . $console->id,
            'type' => 'required|in:PS4,PS5,VR',
            'status' => 'required|in:available,busy,maintenance',
        ]);

        $console->update($validated);
        return back()->with('success', 'Console berhasil diperbarui.');
    }

    public function destroyConsole($id)
    {
        $console = Console::findOrFail($id);
        $console->delete();
        return back()->with('success', 'Console berhasil dihapus.');
    }

    public function updateConsoleTypePrice(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:PS4,PS5,VR',
            'price_per_hour' => 'required|integer|min:0',
        ]);

        Console::where('type', $validated['type'])
            ->update(['price_per_hour' => $validated['price_per_hour']]);

        return back()->with('success', 'Harga ' . $validated['type'] . ' berhasil diperbarui.');
    }
}