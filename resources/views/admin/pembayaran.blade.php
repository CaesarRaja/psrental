<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Admin</title>
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
                <li><a href="{{ route('admin.reservasi') }}">
                    <i class="fas fa-calendar-alt"></i> Manajemen Reservasi
                </a></li>
                <li><a href="{{ route('admin.antrian') }}">
                    <i class="fas fa-list-ol"></i> Sistem Antrian
                </a></li>
                <li><a href="{{ route('admin.pembayaran') }}" class="active">
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
                    <h2>Pembayaran </h2>
                    <p class="text-muted mb-0">Kelola semua transaksi pembayaran</p>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="stats-grid mb-4">
                <div class="stat-card">
                    <div class="stat-info">
                        <h6>Total Pendapatan Hari Ini</h6>
                        <h3>Rp {{ number_format($todayRevenue ?? 0) }}</h3>
                    </div>
                    <div class="stat-icon icon-success">
                        <i class="fas fa-coins"></i>
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
                <div class="stat-card">
                    <div class="stat-info">
                        <h6>Transaksi Berhasil</h6>
                        <h3>{{ $successfulPayments ?? 0 }}</h3>
                    </div>
                    <div class="stat-icon icon-primary">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="dashboard-card mb-4">
                <div class="card-body-custom">
                    <form method="GET" action="{{ route('admin.pembayaran') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control" style="background: #1e293b; border-color: #334155; color: white;">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Metode</label>
                            <select name="method" class="form-control" style="background: #1e293b; border-color: #334155; color: white;">
                                <option value="">Semua Metode</option>
                                <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="transfer" {{ request('method') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                                <option value="qris" {{ request('method') === 'qris' ? 'selected' : '' }}>QRIS</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="date" class="form-control" value="{{ request('date') }}" style="background: #1e293b; border-color: #334155; color: white;">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-2"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-receipt me-2"></i>Daftar Pembayaran</h5>
                </div>
                <div class="card-body-custom p-0">
                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Tipe</th>
                                    <th>Reservasi</th>
                                    <th>Metode</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments ?? [] as $payment)
                                <tr>
                                    <td><strong>#{{ $payment->id }}</strong></td>
                                    <td>{{ $payment->customer->name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $payment->payable_type === 'food' ? 'warning' : 'info' }}">
                                            {{ $payment->payable_type === 'food' ? 'Makanan' : 'Reservasi' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($payment->reservation)
                                            #{{ $payment->reservation_id }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <i class="fas fa-{{ $payment->method === 'cash' ? 'money-bill-wave' : ($payment->method === 'qris' ? 'qrcode' : 'university') }} me-1"></i>
                                            {{ ucfirst($payment->method) }}
                                        </span>
                                    </td>
                                    <td><strong>Rp {{ number_format($payment->total) }}</strong></td>
                                    <td>
                                        <span class="status-badge status-{{ $payment->status }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($payment->created_at)->format('d M Y H:i') }}</td>
                                    <td>
                                        @if($payment->status === 'pending')
                                        <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#confirmModal{{ $payment->id }}">
                                            <i class="fas fa-check"></i> Konfirmasi
                                        </button>
                                        @else
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#detailModal{{ $payment->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        Belum ada pembayaran
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($payments->hasPages())
                    <div class="p-3">
                        {{ $payments->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <!-- Payment Confirmation Modals -->
    @forelse($payments ?? [] as $payment)
    @if($payment->status === 'pending')
    <div class="modal fade" id="confirmModal{{ $payment->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                <div class="modal-header" style="border-color: var(--border-color);">
                    <h5 class="modal-title">Konfirmasi Pembayaran #{{ $payment->id }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-money-bill-wave fa-3x text-success mb-3"></i>
                        <h4>Rp {{ number_format($payment->total) }}</h4>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="text-muted">Customer</label>
                            <h6>{{ $payment->customer->name }}</h6>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="text-muted">Metode</label>
                            <h6>{{ ucfirst($payment->method) }}</h6>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="text-muted">Tipe</label>
                            <h6>{{ $payment->payable_type === 'food' ? 'Makanan' : 'Reservasi' }}</h6>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="text-muted">Tanggal</label>
                            <h6>{{ \Carbon\Carbon::parse($payment->created_at)->format('d M Y H:i') }}</h6>
                        </div>
                        @if($payment->proof_image)
                        <div class="col-12">
                            <label class="text-muted">Bukti Pembayaran</label>
                            <img src="{{ asset('storage/' . $payment->proof_image) }}" class="img-fluid rounded" alt="Bukti Pembayaran">
                        </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer" style="border-color: var(--border-color);">
                    <form action="{{ route('admin.pembayaran.confirm', $payment->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-2"></i> Konfirmasi Pembayaran
                        </button>
                    </form>
                    <form action="{{ route('admin.pembayaran.cancel', $payment->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Yakin ingin membatalkan pembayaran?')">
                            <i class="fas fa-times me-2"></i> Tolak
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="modal fade" id="detailModal{{ $payment->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                <div class="modal-header" style="border-color: var(--border-color);">
                    <h5 class="modal-title">Detail Pembayaran #{{ $payment->id }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="text-muted">Customer</label>
                            <h6>{{ $payment->customer->name }}</h6>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="text-muted">Total</label>
                            <h5 class="text-success">Rp {{ number_format($payment->total) }}</h5>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="text-muted">Metode</label>
                            <h6>{{ ucfirst($payment->method) }}</h6>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="text-muted">Status</label>
                            <span class="status-badge status-{{ $payment->status }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="text-muted">Tipe</label>
                            <h6>{{ $payment->payable_type === 'food' ? 'Makanan' : 'Reservasi' }}</h6>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="text-muted">Tanggal</label>
                            <h6>{{ \Carbon\Carbon::parse($payment->created_at)->format('d M Y H:i') }}</h6>
                        </div>
                        @if($payment->proof_image)
                        <div class="col-12">
                            <label class="text-muted">Bukti Pembayaran</label>
                            <img src="{{ asset('storage/' . $payment->proof_image) }}" class="img-fluid rounded" alt="Bukti Pembayaran">
                        </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer" style="border-color: var(--border-color);">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endif
    @empty
    @endforelse

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>