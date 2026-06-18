<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Food;
use App\Models\FoodOrder;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FoodController extends Controller
{
    public function makanan()
    {
        $foods = Food::oldest()->get();
        $foodOrders = FoodOrder::with('customer')->oldest()->get();
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

    public function destroyFoodOrder($id)
    {
        $order = FoodOrder::findOrFail($id);

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
}
