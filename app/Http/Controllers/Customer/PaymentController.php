<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Payment;
use App\Models\FoodOrder;
use App\Models\PaymentSetting;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function pembayaran()
    {
        $user = Auth::user();

        $reservations = Reservation::where('user_id', $user->id)
            ->whereIn('status', ['active', 'completed'])
            ->with(['payment', 'billingExtensions'])
            ->latest()
            ->paginate(10);

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

        $pendingFoodOrders = FoodOrder::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $paymentSettings = PaymentSetting::first();

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
            ->with('billingExtensions')
            ->firstOrFail();

        $grandTotal = $reservation->grandInvoiceTotal();

        $existingPayment = Payment::where('reservation_id', $reservation->id)->first();

        $updateData = [
            'user_id' => Auth::id(),
            'reservation_id' => $reservation->id,
            'total' => $grandTotal,
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
            'Pembayaran dari ' . Auth::user()->name . ' untuk reservasi #' . $reservation->id . ' senilai Rp ' . number_format($grandTotal),
            route('admin.pembayaran'),
            'payment',
            $paymentId
        );

        return back()->with('success', 'Pembayaran berhasil dikirim. Menunggu konfirmasi admin.');
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
}
