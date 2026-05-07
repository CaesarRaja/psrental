<?php

namespace App\Http\Controllers;

use App\Models\BillingExtension;
use App\Models\Reservation;
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

        BillingExtension::create([
            'reservation_id' => $request->reservation_id,
            'requested_duration' => $request->requested_duration,
            'status' => 'pending',
        ]);

        return response()->json(['success' => 'Request submitted']);
    }

    public function approve($id)
    {
        $extension = BillingExtension::findOrFail($id);
        $extension->update(['status' => 'approved']);

        // Update extended_duration di reservation
        $reservation = $extension->reservation;
        $reservation->increment('extended_duration', $extension->requested_duration);

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

        return redirect()->back()->with('success', 'Extension rejected');
    }
}
