<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Antrian - Admin</title>
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
                <li><a href="{{ route('admin.antrian') }}" class="active">
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
                    <h2>Sistem Antrian 🎫</h2>
                    <p class="text-muted mb-0">Kelola antrian customer secara real-time</p>
                </div>
            </div>

            <!-- Current Queue Display -->
            <div class="dashboard-card mb-4">
                <div class="card-body-custom">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            <div class="queue-number">
                                <div class="number" id="currentQueueNumber">{{ $currentQueueNumber ?? '00' }}</div>
                                <div class="label">Sekarang Dilayani</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">Antrian Menunggu</h5>
                            <div class="queue-list">
                                @forelse($queue ?? [] as $item)
                                <div class="queue-item d-flex justify-content-between align-items-center p-3 mb-2"
                                     style="background: #f8fafc; border-radius: 8px; border-left: 4px solid var(--primary);">
                                    <div>
                                        <strong>#{{ $item->queue_number }}</strong>
                                        <span class="text-muted ms-2">{{ $item->customer->name }}</span>
                                        <span class="badge bg-primary ms-2">{{ $item->console_type }}</span>
                                    </div>
                                    <div>
                                        <small class="text-muted">{{ $item->created_at->diffForHumans() }}</small>
                                        <form action="{{ route('admin.antrian.call', $item->id) }}" method="POST" class="d-inline ms-2">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fas fa-bell"></i> Panggil
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @empty
                                <p class="text-muted">Tidak ada antrian</p>
                                @endforelse
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="p-3" style="background: #ecfdf5; border-radius: 12px;">
                                <h3 class="text-success">{{ count($queue ?? []) }}</h3>
                                <p class="text-muted mb-0">Menunggu</p>
                            </div>
                            <div class="p-3 mt-3" style="background: #dbeafe; border-radius: 12px;">
                                <h3 class="text-primary">{{ $todayCompleted ?? 0 }}</h3>
                                <p class="text-muted mb-0">Selesai Hari Ini</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Queue Controls -->
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-cog me-2"></i>Kontrol Antrian</h5>
                </div>
                <div class="card-body-custom">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <form action="{{ route('admin.antrian.next') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-submit w-100" style="background: linear-gradient(135deg, #10b981, #059669);">
                                    <i class="fas fa-forward me-2"></i> Panggil Berikutnya
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <form action="{{ route('admin.antrian.reset') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-submit w-100" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                                    <i class="fas fa-redo me-2"></i> Reset Antrian
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn-submit w-100" style="background: linear-gradient(135deg, #7c3aed, #6d28d9);"
                                    data-bs-toggle="modal" data-bs-target="#manualQueueModal">
                                <i class="fas fa-plus me-2"></i> Tambah Manual
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>

    <!-- Manual Queue Modal -->
    <div class="modal fade" id="manualQueueModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                <div class="modal-header" style="border-color: var(--border-color);">
                    <h5 class="modal-title">Tambah Antrian Manual</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.antrian.add') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Pilih Customer</label>
                            <select name="user_id" class="form-select" required style="background: #1e293b; border-color: #334155; color: white;">
                                <option value="">-- Pilih Customer --</option>
                                @php
                                $customers = App\Models\User::where('role', 'customer')->get();
                                @endphp
                                @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipe Console</label>
                            <select name="console_type" class="form-select" required style="background: #1e293b; border-color: #334155; color: white;">
                                <option value="PS4">PS4</option>
                                <option value="PS5">PS5</option>
                                <option value="VR">VR</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-color: var(--border-color);">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-submit">Tambah ke Antrian</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>