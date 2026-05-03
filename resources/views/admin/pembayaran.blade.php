@extends('layouts.app')

@section('title', 'Pembayaran - Admin')

@section('sidebar')
    @include('partials.sidebar', ['type' => 'admin', 'active' => 'pembayaran'])
@endsection

@section('header')
    <div class="main-header">
        <div>
            <h2>Kelola Pembayaran</h2>
            <p class="text-muted mb-0">Tinjau dan konfirmasi pembayaran customer</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="dashboard-card">
        <div class="card-header-custom">
            <h5><i class="fas fa-credit-card me-2"></i>Daftar Pembayaran</h5>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments ?? [] as $payment)
                        <tr>
                            <td><strong>#{{ $payment->id }}</strong></td>
                            <td>{{ $payment->customer->name ?? '-' }}</td>
                            <td><strong>Rp {{ number_format($payment->total) }}</strong></td>
                            <td><span class="status-badge status-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($payment->created_at)->format('d M Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada pembayaran</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
