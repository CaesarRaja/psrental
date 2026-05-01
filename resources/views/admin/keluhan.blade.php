<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keluhan Customer - Admin</title>
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
                <li><a href="{{ route('admin.pembayaran') }}">
                    <i class="fas fa-credit-card"></i> Pembayaran
                </a></li>
                <li class="nav-label">Makanan & Minuman</li>
                <li><a href="{{ route('admin.makanan') }}">
                    <i class="fas fa-utensils"></i> Kelola Stok Makanan
                </a></li>
                <li class="nav-label">Lainnya</li>
                <li><a href="{{ route('admin.keluhan') }}" class="active">
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
                    <h2>Keluhan Customer 📝</h2>
                    <p class="text-muted mb-0">Respons dan kelola keluhan dari customer</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="dashboard-card">
                        <div class="card-header-custom">
                            <h5><i class="fas fa-list me-2"></i>Daftar Keluhan</h5>
                        </div>
                        <div class="card-body-custom">
                            @forelse($complaints ?? [] as $complaint)
                            <div class="complaint-item mb-3 p-3" style="border: 1px solid #e2e8f0; border-radius: 8px;">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong>{{ $complaint->customer->name }}</strong>
                                        <span class="badge bg-primary ms-2">{{ $complaint->category }}</span>
                                        <span class="badge bg-{{ $complaint->priority === 'high' ? 'danger' : 'warning' }} ms-1">
                                            {{ ucfirst($complaint->priority) }}
                                        </span>
                                    </div>
                                    <span class="status-badge status-{{ $complaint->status }}">
                                        {{ ucfirst($complaint->status) }}
                                    </span>
                                </div>
                                <h6 class="mb-1">{{ $complaint->subject }}</h6>
                                <p class="text-muted mb-2 small">{{ $complaint->message }}</p>
                                @if($complaint->response)
                                <div class="mt-2 p-2" style="background: #f0fdf4; border-radius: 6px;">
                                    <small class="fw-semibold text-success">Respon Admin:</small>
                                    <p class="mb-0 small">{{ $complaint->response }}</p>
                                </div>
                                @endif
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ \Carbon\Carbon::parse($complaint->created_at)->diffForHumans() }}
                                    </small>
                                </div>
                                @if($complaint->status === 'open')
                                <button class="btn btn-sm btn-primary mt-2"
                                        data-bs-toggle="modal" data-bs-target="#responseModal{{ $complaint->id }}">
                                    <i class="fas fa-reply me-1"></i> Balas
                                </button>
                                @endif
                            </div>

                            <!-- Response Modal -->
                            <div class="modal fade" id="responseModal{{ $complaint->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                                        <div class="modal-header" style="border-color: var(--border-color);">
                                            <h5 class="modal-title">Balas Keluhan #{{ $complaint->id }}</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.keluhan.response', $complaint->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Respon</label>
                                                    <textarea name="response" class="form-control" rows="4" required
                                                              style="background: #1e293b; border-color: #334155; color: white;"
                                                              placeholder="Tulis respon kamu di sini..."></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select" style="background: #1e293b; border-color: #334155; color: white;">
                                                        <option value="resolved">Resolved</option>
                                                        <option value="in_progress">In Progress</option>
                                                        <option value="closed">Closed</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer" style="border-color: var(--border-color);">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn-submit">Kirim Respon</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-check-circle fa-2x mb-2 d-block text-success"></i>
                                <h5>Tidak ada keluhan</h5>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="dashboard-card">
                        <div class="card-header-custom">
                            <h5><i class="fas fa-chart-pie me-2"></i>Statistik Keluhan</h5>
                        </div>
                        <div class="card-body-custom">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Open</span>
                                    <strong class="text-danger">{{ $openComplaints ?? 0 }}</strong>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-danger" style="width: {{ $openPercentage ?? 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>In Progress</span>
                                    <strong class="text-warning">{{ $progressComplaints ?? 0 }}</strong>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-warning" style="width: {{ $progressPercentage ?? 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-0">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Resolved</span>
                                    <strong class="text-success">{{ $resolvedComplaints ?? 0 }}</strong>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: {{ $resolvedPercentage ?? 0 }}%"></div>
                                </div>
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