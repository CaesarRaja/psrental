<?php

namespace App\Http\Controllers;

use App\Models\BillingExtension;
use App\Models\Reservation;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingExtensionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'requested_duration' => 'required|integer|min:1|max:120', // max 2 jam
        ]);

        $reservation = Reservation::find($request->reservation_id);

        // Pastikan user adalah pemilik reservasi
        if ($reservation->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Pastikan reservasi aktif
        if ($reservation->status !== 'active') {
            return response()->json(['error' => 'Reservation not active'], 400);
        }

        $extension = BillingExtension::create([
            'reservation_id' => $request->reservation_id,
            'requested_duration' => $request->requested_duration,
            'status' => 'pending',
        ]);

        NotificationService::notifyAdmins(
            'Permintaan Tambah Billing',
            'Customer meminta tambahan waktu ' . $request->requested_duration . ' menit untuk reservasi #' . $reservation->id,
            route('admin.dashboard'),
            'billing_extension',
            $extension->id
        );

        return response()->json(['success' => 'Request submitted']);
    }

    public function approve($id)
    {
        $extension = BillingExtension::findOrFail($id);
        $extension->update(['status' => 'approved']);

        // Update extended_duration di reservation
        $reservation = $extension->reservation;
        $reservation->increment('extended_duration', $extension->requested_duration);

        NotificationService::notifyCustomer(
            $reservation->user_id,
            'Tambah Billing Disetujui',
            'Permintaan tambahan waktu ' . $extension->requested_duration . ' menit kamu telah disetujui.',
            route('customer.dashboard'),
            'billing_extension',
            $extension->id
        );

        return redirect()->back()->with('success', 'Extension approved');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:255',
        ]);

        $extension = BillingExtension::findOrFail($id);
        $extension->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
        ]);

        $reservation = $extension->reservation;
        $message = 'Permintaan tambahan waktu kamu ditolak.';
        if ($request->admin_notes) {
            $message .= ' Alasan: ' . $request->admin_notes;
        }

        NotificationService::notifyCustomer(
            $reservation->user_id,
            'Tambah Billing Ditolak',
            $message,
            route('customer.dashboard'),
            'billing_extension',
            $extension->id
        );

        return redirect()->back()->with('success', 'Extension rejected');
    }
}
