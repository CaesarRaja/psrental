<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Food;
use App\Models\FoodOrder;
use App\Models\Complaint;
use App\Models\Queue;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // Queue status
        $queue = Queue::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->first();

        return view('customer.dashboard', compact(
            'activeReservations', 'totalReservations', 'totalSpent',
            'loyaltyPoints', 'recentReservations', 'queue'
        ));
    }

    public function reservasi()
    {
        $reservations = Reservation::where('user_id', Auth::id())
            ->latest()->paginate(10);

        return view('customer.reservasi', compact('reservations'));
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
        $foods = Food::where('stock', '>', 0)->orWhere('category', 'Minuman')->get();
        $orders = FoodOrder::where('user_id', Auth::id())->latest()->take(10)->get();

        return view('customer.makanan', compact('foods', 'orders'));
    }

    public function orderMakanan(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'total' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        // Create order
        $order = FoodOrder::create([
            'user_id' => Auth::id(),
            'items' => json_encode($validated['items']),
            'total' => $validated['total'],
            'notes' => $validated['notes'],
            'status' => 'pending',
        ]);

        // Decrease stock
        foreach ($validated['items'] as $item) {
            $food = Food::find($item['id']);
            if ($food) {
                $food->decrement('stock', $item['qty']);
            }
        }

        return back()->with('success', 'Pesanan berhasil dibuat!');
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
        
        return view('customer.pembayaran', compact(
            'reservations', 'paymentHistory', 'totalSpent', 'pendingPayments'
        ));
    }

    public function storePayment(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'method' => 'required|in:cash,transfer,qris',
            'proof_image' => 'nullable|image|max:2048',
        ]);

        $reservation = Reservation::where('id', $validated['reservation_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Check if payment already exists
        $existingPayment = Payment::where('reservation_id', $reservation->id)->first();
        
        $updateData = [
            'user_id' => Auth::id(),
            'reservation_id' => $reservation->id,
            'total' => $reservation->total_price,
            'method' => $validated['method'],
            'status' => 'pending',
            'payable_type' => 'reservation',
            'payable_id' => $reservation->id,
        ];

        if ($request->hasFile('proof_image')) {
            $path = $request->file('proof_image')->store('payments');
            $updateData['proof_image'] = $path;
        }

        if ($existingPayment) {
            $existingPayment->update($updateData);
        } else {
            Payment::create($updateData);
        }

        return back()->with('success', 'Pembayaran berhasil dikirim. Menunggu konfirmasi admin.');
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
            $path = $request->file('attachment')->store('complaints');
            $complaint->update(['attachment' => $path]);
        }

        return back()->with('success', 'Keluhan berhasil dikirim. Admin akan segera merespons.');
    }
}