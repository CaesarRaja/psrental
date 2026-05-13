<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Console;
use App\Models\FoodOrder;
use App\Models\Payment;
use App\Models\Queue;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReservationController extends Controller
{
    public function reservasi()
    {
        $reservations = Reservation::where('user_id', Auth::id())
            ->latest()->paginate(10);

        $consoleTypes = Console::query()
            ->selectRaw('type, MAX(price_per_hour) as price_per_hour')
            ->groupBy('type')
            ->orderBy('type')
            ->get();

        return view('customer.reservasi', compact('reservations', 'consoleTypes'));
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

        $hasAvailableSlot = Console::where('type', $validated['console_type'])
            ->where('status', 'available')
            ->exists();

        if (!$hasAvailableSlot) {
            return redirect()->route('customer.reservasi')->with(
                'console_full',
                'Mohon maaf, console ' . $validated['console_type'] . ' sedang penuh sehingga kamu belum dapat bermain saat ini. Pantau nomor antrian lewat tautan di bawah, atau buka menu Dashboard.'
            );
        }

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

    public function showInvoice($id)
    {
        \Log::info("Show invoice called for reservation ID: {$id}, User ID: " . Auth::id());

        $reservation = Reservation::where('id', $id)
            ->where('user_id', Auth::id())
            ->with('billingExtensions')
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
}
