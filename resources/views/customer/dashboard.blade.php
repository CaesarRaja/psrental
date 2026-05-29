@extends('layouts.app')

@section('title', 'Dashboard Customer - PS Rent Station')

@section('sidebar')
    @include('partials.sidebar', ['type' => 'customer', 'active' => 'dashboard'])
@endsection

@section('header')
    <div>
        <h2>Selamat Datang, {{ Auth::user()->name }}! 👋</h2>
        <p class="text-muted mb-0">Ini dashboard reservasi PlayStation kamu</p>
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
        <div class="stat-card">
            <div class="stat-info">
                <h6>Pesanan Makanan Pending</h6>
                <h3>{{ $pendingFoodOrders ?? 0 }}</h3>
            </div>
            <div class="stat-icon icon-warning">
                <i class="fas fa-utensils"></i>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="card-header-custom">
            <h5><i class="fas fa-clock me-2"></i>Status Antrian</h5>
            @if($myQueue)
                @if($myQueue->status === 'serving')
                    <span class="status-badge status-active"><i class="fas fa-play-circle me-1"></i>Sedang Main</span>
                @else
                    <span class="status-badge status-pending"><i class="fas fa-hourglass-half me-1"></i>Menunggu</span>
                @endif
            @endif
        </div>
        <div class="card-body-custom">
            @if($myQueue)
                <div class="row align-items-center">
                    <div class="col-md-4 text-center">
                        <div class="queue-number">
                            <div class="number">{{ $queueNumber }}</div>
                            <div class="label">Nomor Antrian Kamu</div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Antrian Sekarang</span>
                                <strong class="text-primary fs-5">{{ $currentQueue }}</strong>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-primary" style="width: {{ $queueProgress }}%">{{ $queueProgress }}%</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 p-3 bg-light rounded">
                            <i class="fas fa-info-circle text-primary"></i>
                            <span>
                                @if($myQueue->status === 'serving')
                                    <strong class="text-success">Giliran kamu sekarang!</strong> Silakan ke lokasi.
                                @else
                                    Estimasi waktu tunggu: <strong>{{ $waitTime }}</strong>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3 d-block"></i>
                    <h6 class="text-muted">Kamu belum memiliki nomor antrian hari ini</h6>
                    <p class="text-muted small mb-0">Buat reservasi untuk mendapatkan nomor antrian.</p>
                </div>
            @endif
        </div>
    </div>

    @if($billingTimeRemaining !== null)
    <div class="dashboard-card">
        <div class="card-header-custom">
            <h5><i class="fas fa-clock me-2"></i>Waktu Billing</h5>
        </div>
        <div class="card-body-custom">
            <div class="text-center">
                <div id="billing-timer" class="billing-timer" data-remaining="{{ $billingTimeRemaining }}">
                    <div class="timer-display">
                        <span id="hours">00</span>:<span id="minutes">00</span>:<span id="seconds">00</span>
                    </div>
                    <p class="text-muted mb-3">Waktu bermain tersisa</p>
                    @if($billingTimeRemaining > 0)
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#extendModal">
                            <i class="fas fa-plus-circle me-1"></i>Tambah Waktu
                        </button>
                    @else
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-1"></i>Waktu billing habis!
                            <div class="mt-2">
                                <button class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#extendModal">
                                    <i class="fas fa-plus-circle me-1"></i>Tambah Waktu
                                </button>
                                <a href="{{ route('customer.pembayaran') }}" class="btn btn-success">
                                    <i class="fas fa-credit-card me-1"></i>Bayar Sekarang
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

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

    <div class="dashboard-card mt-4">
        <div class="card-header-custom">
            <h5><i class="fas fa-utensils me-2"></i>Pesanan Makanan Terbaru</h5>
            <a href="{{ route('customer.makanan') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($foodOrders ?? [] as $order)
                        <tr>
                            <td>
                                @foreach($order->items as $item)
                                    <div>{{ $item['name'] ?? 'Item' }} x{{ $item['qty'] ?? 1 }}</div>
                                @endforeach
                            </td>
                            <td>Rp {{ number_format($order->total) }}</td>
                            <td>
                                @switch($order->status)
                                    @case('pending')
                                        <span class="status-badge status-pending">Menunggu</span>
                                        @break
                                    @case('approved')
                                        <span class="status-badge status-active">Diterima</span>
                                        @break
                                    @case('preparing')
                                        <span class="status-badge status-booked">Diproses</span>
                                        @break
                                    @case('delivered')
                                        <span class="status-badge status-completed">Selesai</span>
                                        @break
                                    @case('rejected')
                                        <span class="status-badge status-rejected">Ditolak</span>
                                        @break
                                    @case('cancelled')
                                        <span class="status-badge status-cancelled">Dibatalkan</span>
                                        @break
                                    @default
                                        <span class="status-badge status-pending">{{ ucfirst($order->status) }}</span>
                                @endswitch
                            </td>
                            <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Belum ada pesanan makanan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Extend Billing Modal -->
    <div class="modal fade" id="extendModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Waktu Billing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="extendForm">
                    <div class="modal-body">
                        @php
                            $pricePerHour = $activeReservation ? ($activeReservation->console_type === 'PS4' ? 15000 : ($activeReservation->console_type === 'PS5' ? 25000 : 35000)) : 0;
                            $pricePerMinute = $pricePerHour / 60;
                        @endphp
                        <div class="mb-3">
                            <label for="extendDuration" class="form-label">Durasi Tambahan (menit)</label>
                            <input type="number" class="form-control" id="extendDuration" name="requested_duration" min="10" max="120" step="10" required placeholder="Contoh: 30, 60, 90" oninput="calculateExtendPrice()">
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Harga per menit: Rp {{ number_format($pricePerMinute, 0, ',', '.') }}</small>
                        </div>
                        <div class="mb-3 p-2 bg-light rounded">
                            <strong>Total Harga Tambahan:</strong> <span id="extendTotalPrice" class="text-primary">Rp 0</span>
                        </div>
                        <input type="hidden" id="extendPricePerMinute" value="{{ $pricePerMinute }}">
                        <p class="text-muted">Permintaan akan dikirim ke admin untuk approval.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Kirim Permintaan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const timerElement = document.getElementById('billing-timer');
    if (timerElement) {
        const remaining = parseInt(timerElement.dataset.remaining);
        startCountdown(remaining);
    }

    // Handle extend form submission
    const extendForm = document.getElementById('extendForm');
    if (extendForm) {
        extendForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('reservation_id', {{ $activeReservation->id ?? 'null' }});
            
            fetch('{{ route("customer.billing-extension.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Permintaan tambah waktu berhasil dikirim!');
                    bootstrap.Modal.getInstance(document.getElementById('extendModal')).hide();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengirim permintaan.');
            });
        });
    }
});

function startCountdown(remainingSeconds) {
    const hoursElement = document.getElementById('hours');
    const minutesElement = document.getElementById('minutes');
    const secondsElement = document.getElementById('seconds');

    // Jika sudah habis saat halaman di-load, tampilkan 00:00:00 tanpa reload
    if (remainingSeconds <= 0) {
        hoursElement.textContent = '00';
        minutesElement.textContent = '00';
        secondsElement.textContent = '00';
        return;
    }

    function updateTimer() {
        const hours = Math.floor(remainingSeconds / 3600);
        const minutes = Math.floor((remainingSeconds % 3600) / 60);
        const seconds = remainingSeconds % 60;

        hoursElement.textContent = hours.toString().padStart(2, '0');
        minutesElement.textContent = minutes.toString().padStart(2, '0');
        secondsElement.textContent = seconds.toString().padStart(2, '0');

        if (remainingSeconds > 0) {
            remainingSeconds--;
            setTimeout(updateTimer, 1000);
        } else {
            // Timer baru saja habis dari countdown → reload untuk update UI
            location.reload();
        }
    }

    updateTimer();
}

function calculateExtendPrice() {
    const duration = Number(document.getElementById('extendDuration').value || 0);
    const pricePerMinute = Number(document.getElementById('extendPricePerMinute').value || 0);
    const total = duration * pricePerMinute;
    document.getElementById('extendTotalPrice').textContent = `Rp ${Math.round(total).toLocaleString('id-ID')}`;
}
</script>
@endpush
