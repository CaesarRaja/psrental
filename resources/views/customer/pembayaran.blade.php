<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - PS Rent Station</title>
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
                <li><a href="{{ route('customer.pembayaran') }}" class="active">
                    <i class="fas fa-credit-card"></i> Pembayaran
                </a></li>
                <li class="nav-label">Lainnya</li>
                <li><a href="{{ route('customer.keluhan') }}">
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
                    <h2>Pembayaran 💳</h2>
                    <p class="text-muted mb-0">Lakukan pembayaran setelah bermain</p>
                </div>
            </div>

            <!-- Payment Summary -->
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

            <!-- Reservations Needing Payment -->
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
                                    <th>Status Reservasi</th>
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
                                        <span class="status-badge status-{{ $reservation->status }}">
                                            {{ ucfirst($reservation->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($reservation->payment)
                                            <span class="status-badge status-{{ $reservation->payment->status }}">
                                                {{ ucfirst($reservation->payment->status) }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Belum Bayar</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$reservation->payment || $reservation->payment->status === 'cancelled')
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#payModal{{ $reservation->id }}">
                                            <i class="fas fa-credit-card"></i> Bayar
                                        </button>
                                        @elseif($reservation->payment->status === 'pending')
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-clock me-1"></i> Menunggu
                                        </span>
                                        @else
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i> Lunas
                                        </span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        Tidak ada reservasi yang perlu dibayar
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($reservations->hasPages())
                    <div class="p-3">
                        {{ $reservations->links() }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Payment History -->
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-history me-2"></i>Riwayat Pembayaran</h5>
                </div>
                <div class="card-body-custom p-0">
                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Reservasi</th>
                                    <th>Metode</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentHistory ?? [] as $payment)
                                <tr>
                                    <td><strong>#{{ $payment->id }}</strong></td>
                                    <td>#{{ $payment->reservation_id }}</td>
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
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Belum ada riwayat pembayaran
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($paymentHistory->hasPages())
                    <div class="p-3">
                        {{ $paymentHistory->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <!-- Payment Modals -->
    @forelse($reservations ?? [] as $reservation)
    @if(!$reservation->payment || $reservation->payment->status === 'cancelled')
    <div class="modal fade" id="payModal{{ $reservation->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                <div class="modal-header" style="border-color: var(--border-color);">
                    <h5 class="modal-title">Pembayaran Reservasi #{{ $reservation->id }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('customer.pembayaran.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <div style="font-size: 3rem;">🎮</div>
                            <h4>{{ $reservation->console_type }}</h4>
                            <p class="text-muted">{{ \Carbon\Carbon::parse($reservation->date)->format('d M Y') }} - {{ $reservation->duration }} jam</p>
                            <hr>
                            <h4>Total Pembayaran</h4>
                            <h2 class="text-success">Rp {{ number_format($reservation->total_price) }}</h2>
                        </div>
                        
                        <input type="hidden" name="reservation_id" value="{{ $reservation->id }}">
                        
                        <div class="mb-3">
                            <label class="form-label">Pilih Metode Pembayaran</label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="payment-method-card" onclick="selectMethod('cash', {{ $reservation->id }})">
                                        <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                                        <div>Cash</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="payment-method-card" onclick="selectMethod('transfer', {{ $reservation->id }})">
                                        <i class="fas fa-university fa-2x mb-2"></i>
                                        <div>Transfer</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="payment-method-card" onclick="selectMethod('qris', {{ $reservation->id }})">
                                        <i class="fas fa-qrcode fa-2x mb-2"></i>
                                        <div>QRIS</div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="method" id="method{{ $reservation->id }}" required>
                        </div>
                        
                        <div class="mb-3" id="proofSection{{ $reservation->id }}" style="display: none;">
                            <label class="form-label">Bukti Pembayaran (Opsional)</label>
                            <input type="file" name="proof_image" class="form-control" accept="image/*" style="background: #1e293b; border-color: #334155; color: white;">
                            <small class="text-muted">Upload bukti transfer jika menggunakan transfer</small>
                        </div>
                        
                        <div class="alert alert-info" style="background: #1e3a5f; border: none;">
                            <i class="fas fa-info-circle me-2"></i>
                            Setelah pembayaran, admin akan memverifikasi dan mengkonfirmasi pembayaran kamu.
                        </div>
                    </div>
                    <div class="modal-footer" style="border-color: var(--border-color);">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-paper-plane me-2"></i> Kirim Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    @empty
    @endforelse

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectMethod(method, reservationId) {
            document.getElementById('method' + reservationId).value = method;
            
            const proofSection = document.getElementById('proofSection' + reservationId);
            if (method === 'transfer') {
                proofSection.style.display = 'block';
            } else {
                proofSection.style.display = 'none';
            }
            
            const cards = document.querySelectorAll('#payModal' + reservationId + ' .payment-method-card');
            cards.forEach(card => card.classList.remove('selected'));
            event.currentTarget.classList.add('selected');
        }
    </script>
    <style>
        .payment-method-card {
            border: 2px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-method-card:hover {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.1);
        }
        .payment-method-card.selected {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.2);
        }
    </style>
</body>
</html>