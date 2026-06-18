<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\FoodOrder;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

        $loyaltyPoints = $totalSpent / 1000;

$recentReservations = Reservation::where('user_id', $user->id)
            ->oldest()->take(5)->get();

$foodOrders = FoodOrder::where('user_id', $user->id)
            ->oldest()->take(5)->get();
        $pendingFoodOrders = FoodOrder::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $myQueue = Queue::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->whereIn('status', ['waiting', 'serving'])
            ->first();

        $queueNumber = $myQueue?->queue_number ?? null;
        $currentServing = Queue::whereDate('created_at', today())
            ->where('status', 'serving')
            ->first();
        $currentQueue = $currentServing?->queue_number ?? '00';

        $waitTime = null;
        $queueProgress = 0;
        if ($myQueue) {
            $peopleAhead = Queue::whereDate('created_at', today())
                ->where('status', 'waiting')
                ->where('queue_number', '<', $myQueue->queue_number)
                ->count();
            $waitTime = $peopleAhead > 0
                ? "~" . ($peopleAhead * 5) . " menit"
                : ($myQueue->status === 'serving' ? 'Giliran kamu!' : 'Segera dipanggil');

            $totalWaiting = Queue::whereDate('created_at', today())
                ->whereIn('status', ['waiting', 'serving'])
                ->count();
            $queueProgress = $totalWaiting > 0
                ? round((1 - ($peopleAhead / $totalWaiting)) * 100)
                : 100;
        }

        $activeReservation = Reservation::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        $billingTimeRemaining = null;
        if ($activeReservation) {
            if ($activeReservation->started_at) {
                $startTime = strtotime($activeReservation->started_at);
            } else {
                $startTime = strtotime($activeReservation->date . ' ' . $activeReservation->start_time);
            }
            $totalDurationHours = $activeReservation->duration + ($activeReservation->extended_duration / 60);
            $totalDuration = $totalDurationHours * 3600;
            $endTime = $startTime + $totalDuration;
            $now = time();
            $billingTimeRemaining = max(0, $endTime - $now);
        }

        return view('customer.dashboard', compact(
            'activeReservations', 'totalReservations', 'totalSpent',
            'loyaltyPoints', 'recentReservations', 'billingTimeRemaining', 'activeReservation',
            'foodOrders', 'pendingFoodOrders',
            'queueNumber', 'currentQueue', 'waitTime', 'queueProgress', 'myQueue'
        ));
    }

    public function profile()
    {
        $user = Auth::user();
        return view('customer.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ];

        if ($request->filled('password')) {
            $rules['current_password'] = 'required|string';
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $validated = $request->validate($rules);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ];

        if ($request->filled('password')) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Password lama tidak cocok.']);
            }
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}