@extends('layouts.app')

@section('title', 'Admin Dashboard - PS Rent Station')

@section('sidebar')
    @include('partials.sidebar', ['type' => 'admin', 'active' => 'dashboard'])
@endsection

@section('header')
    <div class="main-header">
        <div>
            <h2>Admin Dashboard 🛡️</h2>
            <p class="text-muted mb-0">Kelola sistem rental PlayStation kamu</p>
        </div>
        <div class="header-actions">
            <div class="notification-btn">
                <i class="fas fa-bell"></i>
                <span class="notification-badge">{{ $pendingReservations ?? 0 }}</span>
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
                <h6>Reservasi Hari Ini</h6>
                <h3>{{ $todayReservations ?? 0 }}</h3>
            </div>
            <div class="stat-icon icon-primary">
                <i class="fas fa-calendar-day"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h6>Sedang Bermain</h6>
                <h3>{{ $activePlaying ?? 0 }}</h3>
            </div>
            <div class="stat-icon icon-success">
                <i class="fas fa-gamepad"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h6>Pendapatan Hari Ini</h6>
                <h3>Rp {{ number_format($todayRevenue ?? 0) }}</h3>
            </div>
            <div class="stat-icon icon-warning">
                <i class="fas fa-coins"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h6>Antrian Menunggu</h6>
                <h3>{{ $queueWaiting ?? 0 }}</h3>
            </div>
            <div class="stat-icon icon-danger">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="card-header-custom">
            <h5><i class="fas fa-desktop me-2"></i>Status Console</h5>
            <span class="badge bg-success">{{ $availableConsoles ?? 0 }} Tersedia</span>
        </div>
        <div class="card-body-custom">
            <div class="console-status-grid">
                @foreach($consoles ?? [] as $console)
                <div class="console-status-item {{ $console->status }}">
                    <span class="console-dot"></span>
                    <h6>{{ $console->name }}</h6>
                    <small>{{ ucfirst($console->status) }}</small>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="card-header-custom">
            <h5><i class="fas fa-list me-2"></i>Reservasi Terbaru</h5>
            <a href="{{ route('admin.reservasi') }}" class="btn btn-sm btn-primary">Kelola Semua</a>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Console</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Durasi</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentReservations ?? [] as $reservation)
                        <tr>
                            <td><strong>#{{ $reservation->id }}</strong></td>
                            <td>{{ $reservation->customer->name ?? '-' }}</td>
                            <td>{{ $reservation->console_type }}</td>
                            <td>{{ \Carbon\Carbon::parse($reservation->date)->format('d M Y') }}</td>
                            <td>{{ $reservation->start_time }}</td>
                            <td>{{ $reservation->duration }} jam</td>
                            <td><strong>Rp {{ number_format($reservation->total_price) }}</strong></td>
                            <td><span class="status-badge status-{{ $reservation->status }}">{{ ucfirst($reservation->status) }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Belum ada reservasi</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
