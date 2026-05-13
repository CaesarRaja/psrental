<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Console;
use App\Models\Queue;
use App\Models\Payment;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReservationController extends Controller
{
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

        Payment::create([
            'user_id' => $reservation->user_id,
            'reservation_id' => $reservation->id,
            'total' => $reservation->grandInvoiceTotal(),
            'method' => 'cash',
            'status' => 'completed',
        ]);

        Console::where('type', $reservation->console_type)
            ->where('status', 'busy')
            ->first()?->update(['status' => 'available']);

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

    public function destroyReservasi($id)
    {
        $reservation = Reservation::findOrFail($id);

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
        $reservations = Reservation::all();

        foreach ($reservations as $reservation) {
            if ($reservation->payment && $reservation->payment->proof_image && Storage::disk('public')->exists($reservation->payment->proof_image)) {
                Storage::disk('public')->delete($reservation->payment->proof_image);
            }
        }

        Payment::whereIn('reservation_id', $reservations->pluck('id'))->delete();
        Queue::whereIn('reservation_id', $reservations->pluck('id'))->delete();
        \App\Models\BillingExtension::whereIn('reservation_id', $reservations->pluck('id'))->delete();
        \App\Models\FoodOrder::whereIn('reservation_id', $reservations->pluck('id'))->delete();
        Reservation::query()->delete();

        return back()->with('success', 'Semua reservasi berhasil dihapus.');
    }
}
