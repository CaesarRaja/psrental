<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends Controller
{
    public function keluhan()
    {
        $complaints = Complaint::with('customer')->latest()->get();

        $total = $complaints->count();
        $openComplaints = $complaints->where('status', 'open')->count();
        $progressComplaints = $complaints->where('status', 'in_progress')->count();
        $resolvedComplaints = $complaints->where('status', 'resolved')->count();

        $openPercentage = $total > 0 ? ($openComplaints / $total * 100) : 0;
        $progressPercentage = $total > 0 ? ($progressComplaints / $total * 100) : 0;
        $resolvedPercentage = $total > 0 ? ($resolvedComplaints / $total * 100) : 0;

        return view('admin.keluhan', compact(
            'complaints', 'openComplaints', 'progressComplaints', 'resolvedComplaints',
            'openPercentage', 'progressPercentage', 'resolvedPercentage'
        ));
    }

    public function responseKeluhan(Request $request, $id)
    {
        $validated = $request->validate([
            'response' => 'required|string',
            'status' => 'required|in:resolved,in_progress,closed',
        ]);

        $complaint = Complaint::findOrFail($id);
        $complaint->update([
            'response' => $validated['response'],
            'status' => $validated['status'],
        ]);

        NotificationService::notifyCustomer(
            $complaint->user_id,
            'Keluhan Direspons',
            'Admin telah merespons keluhan kamu: ' . $complaint->subject,
            route('customer.keluhan'),
            'complaint',
            $complaint->id
        );

        return back()->with('success', 'Respon berhasil dikirim.');
    }

    public function destroyKeluhan($id)
    {
        $complaint = Complaint::findOrFail($id);

        if ($complaint->attachment && Storage::disk('public')->exists($complaint->attachment)) {
            Storage::disk('public')->delete($complaint->attachment);
        }
        $complaint->delete();

        return back()->with('success', 'Keluhan berhasil dihapus.');
    }

    public function destroyAllKeluhan()
    {
        $complaints = Complaint::whereNotNull('attachment')->get();
        foreach ($complaints as $complaint) {
            if (Storage::disk('public')->exists($complaint->attachment)) {
                Storage::disk('public')->delete($complaint->attachment);
            }
        }

        Complaint::query()->delete();

        return back()->with('success', 'Semua keluhan berhasil dihapus.');
    }
}
