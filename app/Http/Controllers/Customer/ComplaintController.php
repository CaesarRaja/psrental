<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends Controller
{
    public function keluhan()
    {
        $complaints = Complaint::where('user_id', Auth::id())->oldest()->get();
        return view('customer.keluhan', compact('complaints'));
    }

    public function storeKeluhan(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|in:console,ruangan,pelayanan,makanan,lainnya',
            'priority' => 'required|in:low,medium,high,urgent',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'attachment' => 'nullable|image|max:2048',
        ]);

        $complaint = Complaint::create([
            'user_id' => Auth::id(),
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'open',
        ]);

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('complaints', 'public');
            $complaint->update(['attachment' => $path]);
        }

        NotificationService::notifyAdmins(
            'Keluhan Baru',
            'Keluhan dari ' . Auth::user()->name . ': ' . $validated['subject'],
            route('admin.keluhan'),
            'complaint',
            $complaint->id
        );

        return back()->with('success', 'Keluhan berhasil dikirim. Admin akan segera merespons.');
    }

    public function destroyKeluhan($id)
    {
        $complaint = Complaint::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        if ($complaint->attachment && Storage::disk('public')->exists($complaint->attachment)) {
            Storage::disk('public')->delete($complaint->attachment);
        }
        $complaint->delete();

        return back()->with('success', 'Keluhan berhasil dihapus.');
    }

    public function destroyAllKeluhan()
    {
        $userId = Auth::id();
        $complaints = Complaint::where('user_id', $userId)->whereNotNull('attachment')->get();
        foreach ($complaints as $complaint) {
            if (Storage::disk('public')->exists($complaint->attachment)) {
                Storage::disk('public')->delete($complaint->attachment);
            }
        }

        Complaint::where('user_id', $userId)->delete();

        return back()->with('success', 'Semua keluhan berhasil dihapus.');
    }
}
