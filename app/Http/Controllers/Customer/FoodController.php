<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Food;
use App\Models\FoodOrder;
use App\Models\Reservation;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FoodController extends Controller
{
    public function makanan()
    {
        $foods = Food::where('status', 'available')->get();
        $orders = FoodOrder::where('user_id', Auth::id())->oldest()->get();

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

        $activeReservation = Reservation::where('user_id', Auth::id())
            ->whereIn('status', ['active', 'approved'])
            ->first();

        if (!$activeReservation) {
            return back()->with('error', 'Anda harus memiliki reservasi aktif untuk memesan makanan. Silakan buat reservasi terlebih dahulu.');
        }

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

        $order = FoodOrder::create([
            'user_id' => Auth::id(),
            'reservation_id' => $activeReservation->id,
            'items' => $items,
            'total' => $validated['total'],
            'notes' => $validated['notes'],
            'status' => 'pending',
        ]);

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

        foreach ($order->items as $item) {
            $food = Food::find($item['id'] ?? null);
            if ($food) {
                $food->increment('stock', $item['qty'] ?? 1);
            }
        }

        $order->update(['status' => 'cancelled']);

        return back()->with('success', 'Pesanan makanan berhasil dibatalkan.');
    }
}
