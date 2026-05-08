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
            @include('partials.notifications')
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
                    <small>
                        @switch($console->status)
                            @case('available')
                                Available
                                @break
                            @case('busy')
                                Unavailable
                                @break
                            @case('maintenance')
                                Maintenance
                                @break
                            @default
                                {{ ucfirst($console->status) }}
                        @endswitch
                    </small>
                    @if($console->status === 'busy')
                        @php
                            $consoleReservation = $activeReservations->firstWhere('console_type', $console->type);
                        @endphp
                        @if($consoleReservation)
                            @php
                                if ($consoleReservation->started_at) {
                                    $startTime = strtotime($consoleReservation->started_at);
                                } else {
                                    $startTime = strtotime($consoleReservation->date . ' ' . $consoleReservation->start_time);
                                }
                                $totalDurationHours = $consoleReservation->duration + ($consoleReservation->extended_duration / 60);
                                $totalDuration = $totalDurationHours * 3600;
                                $endTime = $startTime + $totalDuration;
                                $remaining = max(0, $endTime - time());
                                $rH = floor($remaining / 3600);
                                $rM = floor(($remaining % 3600) / 60);
                                $rS = $remaining % 60;
                            @endphp
                            <small class="timer-admin text-success" data-remaining="{{ $remaining }}">
                                {{ sprintf('%02d:%02d:%02d', $rH, $rM, $rS) }}
                            </small>
                        @endif
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    @if($pendingFoodOrders > 0)
    <div class="dashboard-card">
        <div class="card-header-custom">
            <h5><i class="fas fa-utensils me-2"></i>Pesanan Makanan Menunggu</h5>
            <span class="badge bg-warning">{{ $pendingFoodOrders }}</span>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $pendingFoodOrdersList = \App\Models\FoodOrder::with('customer')->where('status', 'pending')->latest()->take(5)->get();
                        @endphp
                        @foreach($pendingFoodOrdersList as $order)
                        <tr>
                            <td>{{ $order->customer->name ?? '-' }}</td>
                            <td>
                                @foreach($order->items as $item)
                                    <div>{{ $item['name'] ?? 'Item' }} x{{ $item['qty'] ?? 1 }}</div>
                                @endforeach
                            </td>
                            <td><strong>Rp {{ number_format($order->total) }}</strong></td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <form method="POST" action="{{ route('admin.makanan.order.update', $order->id) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i> Terima</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.makanan.order.update', $order->id) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-times"></i> Tolak</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 text-center">
                <a href="{{ route('admin.makanan') }}" class="btn btn-sm btn-primary">Lihat Semua Pesanan</a>
            </div>
        </div>
    </div>
    @endif

    @if($pendingBillingExtensions->count() > 0)
    <div class="dashboard-card">
        <div class="card-header-custom">
            <h5><i class="fas fa-clock me-2"></i>Permintaan Tambah Billing</h5>
            <span class="badge bg-warning">{{ $pendingBillingExtensions->count() }}</span>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Reservasi</th>
                            <th>Durasi Tambah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingBillingExtensions as $extension)
                        <tr>
                            <td>{{ $extension->reservation->customer->name }}</td>
                            <td>#{{ $extension->reservation->id }}</td>
                            <td>{{ $extension->requested_duration }} menit</td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <form method="POST" action="{{ route('admin.billing-extension.approve', $extension->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $extension->id }}">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <!-- Reject Modal -->
                        <div class="modal fade" id="rejectModal{{ $extension->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Tolak Permintaan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="{{ route('admin.billing-extension.reject', $extension->id) }}">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Alasan Penolakan</label>
                                                <textarea class="form-control" name="admin_notes" rows="3" placeholder="Opsional"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger">Tolak</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($activeReservations->count() > 0)
    <div class="dashboard-card">
        <div class="card-header-custom">
            <h5><i class="fas fa-gamepad me-2"></i>Customer Sedang Bermain</h5>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Console</th>
                            <th>Waktu Mulai</th>
                            <th>Waktu Tersisa</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activeReservations as $reservation)
                        @php
                            if ($reservation->started_at) {
                                $startTime = strtotime($reservation->started_at);
                            } else {
                                $startTime = strtotime($reservation->date . ' ' . $reservation->start_time);
                            }
                            $totalDurationHours = $reservation->duration + ($reservation->extended_duration / 60);
                            $totalDuration = $totalDurationHours * 3600; // in seconds
                            $endTime = $startTime + $totalDuration;
                            $remaining = max(0, $endTime - time());
                            $remainingHours = floor($remaining / 3600);
                            $remainingMinutes = floor(($remaining % 3600) / 60);
                            $remainingSeconds = $remaining % 60;
                        @endphp
                        <tr>
                            <td>{{ $reservation->customer->name }}</td>
                            <td>{{ $reservation->console_type }}</td>
                            <td>{{ $reservation->start_time }}</td>
                            <td>
                                @if($remaining > 0)
                                    <span class="text-success timer-admin" id="timer-admin-{{ $reservation->id }}" data-remaining="{{ $remaining }}">
                                        {{ str_pad($remainingHours, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($remainingMinutes, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($remainingSeconds, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                @else
                                    <span class="text-danger">Habis</span>
                                @endif
                            </td>
                            <td><span class="status-badge status-active">Active</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const timers = document.querySelectorAll('.timer-admin');
    
    timers.forEach(function(timer) {
        let remaining = parseInt(timer.dataset.remaining);
        
        function updateTimer() {
            if (remaining <= 0) {
                timer.textContent = 'Habis';
                timer.classList.remove('text-success');
                timer.classList.add('text-danger');
                return;
            }
            
            const hours = Math.floor(remaining / 3600);
            const minutes = Math.floor((remaining % 3600) / 60);
            const seconds = remaining % 60;
            
            timer.textContent = 
                hours.toString().padStart(2, '0') + ':' +
                minutes.toString().padStart(2, '0') + ':' +
                seconds.toString().padStart(2, '0');
            
            remaining--;
        }
        
        updateTimer();
        setInterval(updateTimer, 1000);
    });
});
</script>
@endpush
