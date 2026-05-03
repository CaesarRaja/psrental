@extends('layouts.app')

@section('title', 'Dashboard Customer - PS Rent Station')

@section('sidebar')
    @include('partials.sidebar', ['type' => 'customer', 'active' => 'dashboard'])
@endsection

@section('header')
    <div class="main-header">
        <div>
            <h2>Selamat Datang, {{ Auth::user()->name }}! 👋</h2>
            <p class="text-muted mb-0">Ini dashboard reservasi PlayStation kamu</p>
        </div>
        <div class="header-actions">
            <div class="notification-btn">
                <i class="fas fa-bell"></i>
                <span class="notification-badge">0</span>
            </div>
            <a href="{{ route('logout') }}" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <h6>Reservasi Aktif</h6>
                <h3>{{ $activeReservations ?? 0 }}</h3>
            </div>
            <div class="stat-icon icon-primary">
                <i class="fas fa-calendar-check"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h6>Total Reservasi</h6>
                <h3>{{ $totalReservations ?? 0 }}</h3>
            </div>
            <div class="stat-icon icon-success">
                <i class="fas fa-history"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h6>Total Pengeluaran</h6>
                <h3>Rp {{ number_format($totalSpent ?? 0) }}</h3>
            </div>
            <div class="stat-icon icon-warning">
                <i class="fas fa-wallet"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h6>Poin Loyalty</h6>
                <h3>{{ $loyaltyPoints ?? 0 }}</h3>
            </div>
            <div class="stat-icon icon-purple">
                <i class="fas fa-star"></i>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="card-header-custom">
            <h5><i class="fas fa-clock me-2"></i>Status Antrian</h5>
        </div>
        <div class="card-body-custom">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="queue-number">
                        <div class="number">{{ $queueNumber ?? '00' }}</div>
                        <div class="label">Nomor Antrian Kamu</div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="queue-info">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Antrian Sekarang:</span>
                            <strong class="text-primary">{{ $currentQueue ?? '00' }}</strong>
                        </div>
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: {{ $queueProgress ?? 0 }}%"></div>
                        </div>
                        <p class="text-muted mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            Estimasi waktu tunggu: {{ $waitTime ?? 'Tidak ada antrian' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="card-header-custom">
            <h5><i class="fas fa-list me-2"></i>Reservasi Terbaru</h5>
            <a href="{{ route('customer.reservasi') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
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
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentReservations ?? [] as $reservation)
                        <tr>
                            <td><strong>#{{ $reservation->id }}</strong></td>
                            <td>{{ $reservation->console_type }}</td>
                            <td>{{ $reservation->date }}</td>
                            <td>{{ $reservation->duration }} jam</td>
                            <td>
                                <span class="status-badge status-{{ $reservation->status }}">
                                    {{ ucfirst($reservation->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Belum ada reservasi
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
