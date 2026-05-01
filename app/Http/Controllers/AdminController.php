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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard()
    {
        $todayReservations = Reservation::whereDate('created_at', today())->count();
        $activePlaying = Reservation::where('status', 'active')->count();
        $todayRevenue = Reservation::whereDate('created_at', today())
            ->where('status', 'completed')->sum('total_price');
        $queueWaiting = Queue::where('status', 'waiting')->count();
        $totalCustomers = User::where('role', 'customer')->count();
        $newComplaints = Complaint::where('status', 'open')->count();
        $pendingReservations = Reservation::where('status', 'pending')->count();

        $consoles = Console::all();
        $availableConsoles = Console::where('status', 'available')->count();

        $recentReservations = Reservation::with('customer')->latest()->take(10)->get();

        $weeklyReservations = Reservation::whereBetween('created_at', [now()->subWeek(), now()])->count();
        $newCustomers = User::where('role', 'customer')
            ->whereBetween('created_at', [now()->subWeek(), now()])->count();

        return view('admin.dashboard', compact(
            'todayReservations', 'activePlaying', 'todayRevenue',
            'queueWaiting', 'totalCustomers', 'newComplaints',
            'pendingReservations', 'consoles', 'availableConsoles',
            'recentReservations', 'weeklyReservations', 'newCustomers'
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
        return view('admin.reservasi', compact('reservations'));
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

        return back()->with('success', 'Reservasi disetujui.');
    }

    public function rejectReservasi($id)
    {
        Reservation::findOrFail($id)->update(['status' => 'rejected']);
        return back()->with('success', 'Reservasi ditolak.');
    }

    public function startReservasi($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->update(['status' => 'active']);

        // Update console status
        Console::where('name', 'like', $reservation->console_type . '%')
            ->where('status', 'available')->first()?->update(['status' => 'busy']);

        return back()->with('success', 'Sesi dimulai.');
    }

    public function completeReservasi($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->update(['status' => 'completed']);

        // Create payment
        Payment::create([
            'user_id' => $reservation->user_id,
            'reservation_id' => $reservation->id,
            'total' => $reservation->total_price,
            'method' => 'cash',
            'status' => 'completed',
        ]);

        // Update console status
        Console::where('status', 'busy')->first()?->update(['status' => 'available']);

        return back()->with('success', 'Sesi selesai.');
    }

    public function antrian()
    {
        $queue = Queue::where('status', 'waiting')->orderBy('queue_number')->get();
        $currentQueueNumber = Queue::where('status', 'serving')->first()?->queue_number ?? '00';
        $todayCompleted = Queue::whereDate('created_at', today())
            ->where('status', 'completed')->count();

        return view('admin.antrian', compact('queue', 'currentQueueNumber', 'todayCompleted'));
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
        $query = Payment::with('customer');

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
        return back()->with('success', 'Pembayaran dikonfirmasi.');
    }

    public function cancelPayment($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->update(['status' => 'cancelled']);
        return back()->with('success', 'Pembayaran dibatalkan.');
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
            'emoji' => 'required|string',
            'name' => 'required|string|max:255',
            'category' => 'required|in:Makanan,Minuman,Snack',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        Food::create($validated);
        return back()->with('success', 'Makanan berhasil ditambahkan.');
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
        Food::findOrFail($id)->delete();
        return back()->with('success', 'Makanan berhasil dihapus.');
    }

    public function updateFoodOrder(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:preparing,delivered,cancelled',
        ]);

        $order = FoodOrder::findOrFail($id);
        $order->update(['status' => $validated['status']]);

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

        Complaint::findOrFail($id)->update([
            'response' => $validated['response'],
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Respon berhasil dikirim.');
    }
}