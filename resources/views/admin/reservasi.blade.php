<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Reservasi - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar" style="background: #1a1a2e;">
            <div class="sidebar-brand">
                <i class="fas fa-gamepad"></i>
                <h5>PS Rent Station</h5>
                <span class="badge bg-danger ms-2">Admin</span>
            </div>
            <ul class="sidebar-nav">
                <li class="nav-label">Manajemen</li>
                <li><a href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a></li>
                <li><a href="{{ route('admin.reservasi') }}" class="active">
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
                    <div class="user-avatar" style="background: linear-gradient(135deg, #ef4444, #dc2626);">AD</div>
                    <div class="user-details">
                        <h6>{{ Auth::user()->name }}</h6>
                        <small style="color: #ef4444;">Administrator</small>
                    </div>
                </div>
            </div>
        </aside>

        <main class="main-content">
            <div class="main-header">
                <div>
                    <h2>Manajemen Reservasi </h2>
                    <p class="text-muted mb-0">Kelola semua reservasi customer</p>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="dashboard-card mb-4">
                <div class="card-body-custom">
                    <form action="{{ route('admin.reservasi') }}" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Console</label>
                            <select name="console" class="form-select">
                                <option value="">Semua Console</option>
                                <option value="PS4">PS4</option>
                                <option value="PS5">PS5</option>
                                <option value="VR">VR</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn-submit w-100">
                                <i class="fas fa-search me-2"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reservations Table -->
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-list me-2"></i>Daftar Reservasi</h5>
                    <span class="badge bg-primary">{{ $reservations->total() }} Total</span>
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
                                @forelse($reservations ?? [] as $reservation)
                                <tr>
                                    <td><strong>#{{ $reservation->id }}</strong></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.7rem;">
                                                {{ strtoupper(substr($reservation->customer->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div>{{ $reservation->customer->name }}</div>
                                                <small class="text-muted">{{ $reservation->customer->email }}</small>
                                            </div>
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
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.reservasi.reject', $reservation->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Reject">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                            @endif
                                            @if($reservation->status === 'approved')
                                            <form action="{{ route('admin.reservasi.start', $reservation->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Start Session">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            </form>
                                            @endif
                                            @if($reservation->status === 'active')
                                            <form action="{{ route('admin.reservasi.complete', $reservation->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Complete">
                                                    <i class="fas fa-flag-checkered"></i>
                                                </button>
                                            </form>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-outline-info" title="Detail"
                                                    data-bs-toggle="modal" data-bs-target="#detailModal{{ $reservation->id }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        Tidak ada data reservasi
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $reservations->links() }}
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>