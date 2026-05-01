<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keluhan - PS Rent Station</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-gamepad"></i>
                <h5>PS Rent Station</h5>
            </div>
            <ul class="sidebar-nav">
                <li class="nav-label">Menu Utama</li>
                <li><a href="{{ route('customer.dashboard') }}">
                    <i class="fas fa-home"></i> Dashboard
                </a></li>
                <li><a href="{{ route('customer.reservasi') }}">
                    <i class="fas fa-calendar-check"></i> Reservasi
                </a></li>
                <li><a href="{{ route('customer.makanan') }}">
                    <i class="fas fa-utensils"></i> Pesan Makanan
                </a></li>
                <li><a href="{{ route('customer.pembayaran') }}">
                    <i class="fas fa-credit-card"></i> Pembayaran
                </a></li>
                <li class="nav-label">Lainnya</li>
                <li><a href="{{ route('customer.keluhan') }}" class="active">
                    <i class="fas fa-comment-dots"></i> Keluhan
                </a></li>
            </ul>
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                    <div class="user-details">
                        <h6>{{ Auth::user()->name }}</h6>
                        <small>Customer</small>
                    </div>
                </div>
            </div>
        </aside>

        <main class="main-content">
            <div class="main-header">
                <div>
                    <h2>Kirim Keluhan 📝</h2>
                    <p class="text-muted mb-0">Ada masalah? Sampaikan kepada kami</p>
                </div>
            </div>

            <div class="row g-4">
                <!-- Complaint Form -->
                <div class="col-lg-5">
                    <div class="dashboard-card">
                        <div class="card-header-custom">
                            <h5><i class="fas fa-pen me-2"></i>Form Keluhan</h5>
                        </div>
                        <div class="card-body-custom">
                            <form action="{{ route('customer.keluhan.store') }}" method="POST" class="complaint-form">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Kategori Keluhan</label>
                                    <select name="category" class="form-select" required>
                                        <option value="">Pilih kategori</option>
                                        <option value="console">Masalah Console</option>
                                        <option value="ruangan">Masalah Ruangan</option>
                                        <option value="pelayanan">Pelayanan</option>
                                        <option value="makanan">Makanan/Minuman</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Prioritas</label>
                                    <select name="priority" class="form-select" required>
                                        <option value="low">Rendah</option>
                                        <option value="medium" selected>Sedang</option>
                                        <option value="high">Tinggi</option>
                                        <option value="urgent">Sangat Urgent</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Subject</label>
                                    <input type="text" name="subject" class="form-control" placeholder="Judul keluhan kamu" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Detail Keluhan</label>
                                    <textarea name="message" class="form-control" rows="5" placeholder="Jelaskan keluhan kamu secara detail..." required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Lampiran (Opsional)</label>
                                    <input type="file" name="attachment" class="form-control" accept="image/*">
                                </div>
                                <button type="submit" class="btn-submit">
                                    <i class="fas fa-paper-plane me-2"></i> Kirim Keluhan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Complaint History -->
                <div class="col-lg-7">
                    <div class="dashboard-card">
                        <div class="card-header-custom">
                            <h5><i class="fas fa-list me-2"></i>Riwayat Keluhan</h5>
                        </div>
                        <div class="card-body-custom">
                            @forelse($complaints ?? [] as $complaint)
                            <div class="complaint-item mb-3 p-3" style="border: 1px solid #e2e8f0; border-radius: 8px;">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-primary me-2">{{ $complaint->category }}</span>
                                        <span class="badge bg-{{ ($complaint->priority === 'high' ? 'danger' : ($complaint->priority === 'medium' ? 'warning' : 'secondary')) }}">
                                            {{ ucfirst($complaint->priority) }}
                                        </span>
                                    </div>
                                    <span class="status-badge status-{{ $complaint->status }}">
                                        {{ ucfirst($complaint->status) }}
                                    </span>
                                </div>
                                <h6 class="mb-1">{{ $complaint->subject }}</h6>
                                <p class="text-muted mb-2 small">{{ $complaint->message }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ \Carbon\Carbon::parse($complaint->created_at)->diffForHumans() }}
                                    </small>
                                    @if($complaint->response)
                                    <small class="text-success">
                                        <i class="fas fa-check-circle me-1"></i> Sudah direspons
                                    </small>
                                    @endif
                                </div>
                                @if($complaint->response)
                                <div class="mt-2 p-2" style="background: #f0fdf4; border-radius: 6px; border-left: 3px solid #10b981;">
                                    <small class="fw-semibold text-success">Respon Admin:</small>
                                    <p class="mb-0 small">{{ $complaint->response }}</p>
                                </div>
                                @endif
                            </div>
                            @empty
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-comment-slash fa-2x mb-2 d-block"></i>
                                <h5>Belum ada keluhan</h5>
                            </div>
                            @endforelse
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