<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    public function customers(Request $request)
    {
        $search = $request->input('search');
        $query = User::where('role', 'customer');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $customers = $query->latest()->paginate(15);
        return view('admin.customers.index', compact('customers', 'search'));
    }

    public function editCustomer($id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);
        return view('admin.customers.edit', compact('customer'));
    }

    public function updateCustomer(Request $request, $id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ];

        if ($request->filled('password')) {
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
            $updateData['password'] = Hash::make($validated['password']);
        }

        $customer->update($updateData);

        return redirect()->route('admin.customers')
            ->with('success', 'Data customer berhasil diperbarui.');
    }

    public function destroyCustomer($id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);

        $customer->reservations()->delete();
        $customer->foodOrders()->delete();
        $customer->complaints()->delete();
        $customer->payments()->delete();

        $customer->delete();

        return redirect()->route('admin.customers')
            ->with('success', 'Akun customer berhasil dihapus.');
    }
}
