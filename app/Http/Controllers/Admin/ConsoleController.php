<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Console;
use Illuminate\Http\Request;

class ConsoleController extends Controller
{
    public function consoles()
    {
        $consoles = Console::latest()->get();

        $totalConsoles = $consoles->count();
        $availableConsoles = $consoles->where('status', 'available')->count();
        $busyConsoles = $consoles->where('status', 'busy')->count();
        $maintenanceConsoles = $consoles->where('status', 'maintenance')->count();

        $consoleByType = $consoles->groupBy('type')->map(function ($group) {
            return [
                'total' => $group->count(),
                'available' => $group->where('status', 'available')->count(),
                'busy' => $group->where('status', 'busy')->count(),
                'maintenance' => $group->where('status', 'maintenance')->count(),
            ];
        });

        $typePrices = Console::select('type', 'price_per_hour')
            ->distinct()
            ->pluck('price_per_hour', 'type')
            ->toArray();

        return view('admin.consoles', compact(
            'consoles', 'totalConsoles', 'availableConsoles', 'busyConsoles', 'maintenanceConsoles', 'consoleByType', 'typePrices'
        ));
    }

    public function storeConsole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:consoles,name',
            'type' => 'required|in:PS4,PS5,VR',
            'status' => 'required|in:available,busy,maintenance',
        ]);

        $existingPrice = Console::where('type', $validated['type'])->value('price_per_hour');
        $validated['price_per_hour'] = $existingPrice ?? 0;

        Console::create($validated);
        return back()->with('success', 'Console berhasil ditambahkan.');
    }

    public function updateConsole(Request $request, $id)
    {
        $console = Console::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:consoles,name,' . $console->id,
            'type' => 'required|in:PS4,PS5,VR',
            'status' => 'required|in:available,busy,maintenance',
        ]);

        $console->update($validated);
        return back()->with('success', 'Console berhasil diperbarui.');
    }

    public function destroyConsole($id)
    {
        $console = Console::findOrFail($id);
        $console->delete();
        return back()->with('success', 'Console berhasil dihapus.');
    }

    public function updateConsoleTypePrice(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:PS4,PS5,VR',
            'price_per_hour' => 'required|integer|min:0',
        ]);

        Console::where('type', $validated['type'])
            ->update(['price_per_hour' => $validated['price_per_hour']]);

        return back()->with('success', 'Harga ' . $validated['type'] . ' berhasil diperbarui.');
    }
}
