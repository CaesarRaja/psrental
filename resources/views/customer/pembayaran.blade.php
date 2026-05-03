@extends('layouts.app')

@section('title', 'Pembayaran - PS Rent Station')

@section('sidebar')
    @include('partials.sidebar', ['type' => 'customer', 'active' => 'pembayaran'])
@endsection

@section('header')
    <div class="main-header">
        <div>
            <h2>Pembayaran 💳</h2>
            <p class="text-muted mb-0">Lakukan pembayaran setelah bermain</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-info">
                <h6>Total Pembayaran Berhasil</h6>
                <h3>Rp {{ number_format($totalSpent ?? 0) }}</h3>
            </div>
            <div class="stat-icon icon-success">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h6>Pembayaran Pending</h6>
                <h3>{{ $pendingPayments ?? 0 }}</h3>
            </div>
            <div class="stat-icon icon-warning">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>

    <div class="dashboard-card mb-4">
        <div class="card-header-custom">
            <h5><i class="fas fa-gamepad me-2"></i>Reservasi yang Perlu Dibayar</h5>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Console</th>
                            <th>Tanggal</th>
                            <th>Durasi</th>
                            <th>Total</th>
                            <th>Status Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservations ?? [] as $reservation)
                        <tr>
                            <td><strong>#{{ $reservation->id }}</strong></td>
                            <td>{{ $reservation->console_type }}</td>
                            <td>{{ \Carbon\Carbon::parse($reservation->date)->format('d M Y') }}</td>
                            <td>{{ $reservation->duration }} jam</td>
                            <td><strong>Rp {{ number_format($reservation->total_price) }}</strong></td>
                            <td>
                                @if($reservation->payment)
                                    <span class="status-badge status-{{ $reservation->payment->status }}">{{ ucfirst($reservation->payment->status) }}</span>
                                @else
                                    <span class="badge bg-secondary">Belum Bayar</span>
                                @endif
                            </td>
                            <td>
                                @if(!$reservation->payment || $reservation->payment->status === 'cancelled')
                                <button class="btn btn-sm btn-outline-primary" disabled>
                                    <i class="fas fa-credit-card"></i> Bayar
                                </button>
                                @elseif($reservation->payment->status === 'pending')
                                <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Menunggu</span>
                                @else
                                <span class="badge bg-success"><i class="fas fa-check me-1"></i> Lunas</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Tidak ada reservasi yang perlu dibayar</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
