<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function antrian()
    {
        $queues = Queue::with('customer')
            ->where('status', 'waiting')
            ->orderBy('queue_number')
            ->get();

        $currentServing = Queue::with('customer')
            ->where('status', 'serving')
            ->first();

        $todayCompleted = Queue::whereDate('created_at', today())
            ->where('status', 'completed')->count();

        $customers = User::where('role', 'customer')->orderBy('name')->get();

        return view('admin.antrian', compact('queues', 'currentServing', 'todayCompleted', 'customers'));
    }

    public function nextQueue()
    {
        $next = Queue::where('status', 'waiting')->orderBy('queue_number')->first();
        if ($next) {
            Queue::where('status', 'serving')->update(['status' => 'completed']);
            $next->update(['status' => 'serving']);
        }
        return back();
    }

    public function callQueue($id)
    {
        Queue::where('status', 'serving')->update(['status' => 'completed']);
        Queue::findOrFail($id)->update(['status' => 'serving']);
        return back()->with('success', 'Customer dipanggil.');
    }

    public function resetQueue()
    {
        Queue::truncate();
        return back()->with('success', 'Antrian direset.');
    }

    public function currentQueue()
    {
        $queue = Queue::where('status', 'serving')->first();
        return response()->json(['queue_number' => $queue?->queue_number ?? '00']);
    }

    public function addManualQueue(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'console_type' => 'required|in:PS4,PS5,VR',
        ]);

        $lastQueue = Queue::whereDate('created_at', today())
            ->orderBy('queue_number', 'desc')
            ->first();

        $queueNumber = $lastQueue ? intval($lastQueue->queue_number) + 1 : 1;

        Queue::create([
            'user_id' => $validated['user_id'],
            'reservation_id' => null,
            'console_type' => $validated['console_type'],
            'queue_number' => str_pad($queueNumber, 3, '0', STR_PAD_LEFT),
            'status' => 'waiting',
        ]);

        return back()->with('success', 'Antrian berhasil ditambahkan.');
    }
}
