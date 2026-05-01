<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PS Rent Station</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Admin Sidebar -->
        <aside class="sidebar" style="background: #1a1a2e;">
            <div class="sidebar-brand">
                <i class="fas fa-gamepad"></i>
                <h5>PS Rent Station</h5>
                <span class="badge bg-danger ms-2">Admin</span>
            </div>

            <ul class="sidebar-nav">
                <li class="nav-label">Manajemen</li>
                <li><a href="{{ route('admin.dashboard') }}" class="active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a></li>
                <li><a href="{{ route('admin.reservasi') }}">
                    <i class="fas fa-calendar-alt"></i> Manajemen Reservasi
                </a></li>
                <li><a href="{{ route('admin.antrian') }}">
                    <i class="fas fa-list-ol"></i> Sistem Antrian
                </a></li>
                <li><a href="{{ route('admin.pembayaran') }}">
                    <i class="fas fa-credit-card"></i> Pembayaran
                </a></li>
                <li class="nav-label">Makanan & Minuman</li>
                <li><a href="{{ route('admin.makanan') }}">
                    <i class="fas fa-utensils"></i> Kelola Stok Makanan
                </a></li>
                <li class="nav-label">Lainnya</li>
                <li><a href="{{ route('admin.keluhan') }}">
                    <i class="fas fa-comment-dots"></i> Keluhan Customer
                </a></li>
            </ul>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                        AD
                    </div>
                    <div class="user-details">
                        <h6>{{ Auth::user()->name }}</h6>
                        <small style="color: #ef4444;">Administrator</small>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
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

            <!-- Stats -->
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
                <div class="stat-card">
                    <div class="stat-info">
                        <h6>Total Customer</h6>
                        <h3>{{ $totalCustomers ?? 0 }}</h3>
                    </div>
                    <div class="stat-icon icon-purple">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h6>Keluhan Baru</h6>
                        <h3>{{ $newComplaints ?? 0 }}</h3>
                    </div>
                    <div class="stat-icon icon-pink">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>

            <!-- Console Status -->
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

            <!-- Recent Reservations Table -->
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
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentReservations ?? [] as $reservation)
                                <tr>
                                    <td><strong>#{{ $reservation->id }}</strong></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.7rem;">
                                                {{ strtoupper(substr($reservation->customer->name, 0, 2)) }}
                                            </div>
                                            {{ $reservation->customer->name }}
                                        </div>
                                    </td>
                                    <td>{{ $reservation->console_type }}</td>
                                    <td>{{ \Carbon\Carbon::parse($reservation->date)->format('d M Y') }}</td>
                                    <td>{{ $reservation->start_time }}</td>
                                    <td>{{ $reservation->duration }} jam</td>
                                    <td><strong>Rp {{ number_format($reservation->total_price) }}</strong></td>
                                    <td>
                                        <span class="status-badge status-{{ $reservation->status }}">
                                            {{ ucfirst($reservation->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            @if($reservation->status === 'pending')
                                            <form action="{{ route('admin.reservasi.approve', $reservation->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.reservasi.reject', $reservation->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                            @endif
                                            @if($reservation->status === 'approved')
                                            <form action="{{ route('admin.reservasi.start', $reservation->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            </form>
                                            @endif
                                            @if($reservation->status === 'active')
                                            <form action="{{ route('admin.reservasi.complete', $reservation->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-flag-checkered"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        Belum ada reservasi
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="dashboard-card">
                        <div class="card-header-custom">
                            <h5><i class="fas fa-bolt me-2"></i>Aksi Cepat</h5>
                        </div>
                        <div class="card-body-custom">
                            <div class="row g-2">
                                <div class="col-6">
                                    <a href="{{ route('admin.reservasi') }}" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-calendar-alt d-block mb-1"></i> Reservasi
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="{{ route('admin.antrian') }}" class="btn btn-outline-warning w-100">
                                        <i class="fas fa-list-ol d-block mb-1"></i> Antrian
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="{{ route('admin.pembayaran') }}" class="btn btn-outline-success w-100">
                                        <i class="fas fa-credit-card d-block mb-1"></i> Pembayaran
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="{{ route('admin.makanan') }}" class="btn btn-outline-info w-100">
                                        <i class="fas fa-utensils d-block mb-1"></i> Stok Makanan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="dashboard-card">
                        <div class="card-header-custom">
                            <h5><i class="fas fa-chart-line me-2"></i>Ringkasan Minggu Ini</h5>
                        </div>
                        <div class="card-body-custom">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Reservasi</span>
                                <strong>{{ $weeklyReservations ?? 0 }}</strong>
                            </div>
                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: 75%"></div>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Console Terpopuler</span>
                                <strong>PS5</strong>
                            </div>
                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: 60%"></div>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Customer Baru</span>
                                <strong>{{ $newCustomers ?? 0 }}</strong>
                            </div>
                            <div class="progress mb-0" style="height: 6px;">
                                <div class="progress-bar bg-warning" style="width: 45%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>