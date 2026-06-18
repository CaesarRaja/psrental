<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentSetting;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function pembayaran(Request $request)
    {
        $query = Payment::with(['customer', 'reservation.billingExtensions', 'reservation.foodOrders']);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->method) {
            $query->where('method', $request->method);
        }
        if ($request->date) {
            $query->whereDate('created_at', $request->date);
        }

        $payments = $query->oldest()->paginate(15);
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
        $settings = PaymentSetting::first();
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

        $settings = PaymentSetting::firstOrCreate([]);
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

    public function destroyPembayaran($id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->proof_image && Storage::disk('public')->exists($payment->proof_image)) {
            Storage::disk('public')->delete($payment->proof_image);
        }
        $payment->delete();

        return back()->with('success', 'Pembayaran berhasil dihapus.');
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
}
