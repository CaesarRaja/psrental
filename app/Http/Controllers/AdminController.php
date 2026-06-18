<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Reservation;
use App\Models\Food;
use App\Models\FoodOrder;
use App\Models\Payment;
use App\Models\Complaint;
use App\Models\Queue;
use App\Models\Console;
use App\Models\BillingExtension;

class AdminController extends Controller
{
    public function dashboard()
    {
        $activeConsoleTypes = Reservation::where('status', 'active')
            ->distinct('console_type')
            ->pluck('console_type')
            ->toArray();

        Console::where('status', 'busy')
            ->whereNotIn('type', $activeConsoleTypes)
            ->update(['status' => 'available']);

        $todayReservations = Reservation::whereDate('created_at', today())->count();
        $activePlaying = Reservation::where('status', 'active')->count();
        $todayRevenue = Reservation::whereDate('created_at', today())
            ->where('status', 'completed')->sum('total_price');
        $queueWaiting = Queue::where('status', 'waiting')->count();
        $totalCustomers = User::where('role', 'customer')->count();
        $newComplaints = Complaint::where('status', 'open')->count();
        $pendingReservations = Reservation::where('status', 'pending')->count();
        $pendingFoodOrders = FoodOrder::where('status', 'pending')->count();

        $consoles = Console::all();
        $availableConsoles = Console::where('status', 'available')->count();
        $busyConsoles = Console::where('status', 'busy')->count();
        $maintenanceConsoles = Console::where('status', 'maintenance')->count();

        $consoleByType = $consoles->groupBy('type')->map(function ($group) {
            return [
                'total' => $group->count(),
                'available' => $group->where('status', 'available')->count(),
                'busy' => $group->where('status', 'busy')->count(),
                'maintenance' => $group->where('status', 'maintenance')->count(),
            ];
        });

        $recentReservations = Reservation::with('customer')->oldest()->take(10)->get();

        $weeklyReservations = Reservation::whereBetween('created_at', [now()->subWeek(), now()])->count();
        $newCustomers = User::where('role', 'customer')
            ->whereBetween('created_at', [now()->subWeek(), now()])->count();

        $pendingBillingExtensions = BillingExtension::with('reservation.customer')
            ->where('status', 'pending')->get();

        $activeReservations = Reservation::with('customer')
            ->where('status', 'active')->get();

        return view('admin.dashboard', compact(
            'todayReservations', 'activePlaying', 'todayRevenue',
            'queueWaiting', 'totalCustomers', 'newComplaints',
            'pendingReservations', 'pendingFoodOrders', 'consoles', 'availableConsoles',
            'busyConsoles', 'maintenanceConsoles', 'consoleByType',
            'recentReservations', 'weeklyReservations', 'newCustomers',
            'pendingBillingExtensions', 'activeReservations'
        ));
    }
}