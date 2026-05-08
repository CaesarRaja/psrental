@extends('layouts.app')

@section('title', 'Reservasi - PS Rent Station')

@section('sidebar')
    @include('partials.sidebar', ['type' => 'customer', 'active' => 'reservasi'])
@endsection

@section('header')
    <div class="main-header">
        <div>
            <h2>Reservasi PlayStation 🎮</h2>
            <p class="text-muted mb-0">Pilih console dan buat reservasi kamu</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('customer.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="dashboard-card">
        <div class="card-header-custom">
            <h5><i class="fas fa-edit me-2"></i>Form Reservasi</h5>
        </div>
        <div class="card-body-custom">
            <form action="{{ route('customer.reservasi.store') }}" method="POST" id="reservationForm">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Console Dipilih</label>
                        <select name="console_type" id="console_type" class="form-select" required onchange="calculateTotal()">
                            <option value="">Pilih console</option>
                            @foreach($consoles as $console)
                                @php
                                    $price = $console->type === 'PS4' ? 15000 : ($console->type === 'PS5' ? 25000 : 35000);
                                @endphp
                                <option value="{{ $console->type }}" data-price="{{ $price }}">{{ $console->name }} ({{ $console->type }}) – Rp {{ number_format($price) }}/jam</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Reservasi</label>
                        <input type="date" name="date" class="form-control" required min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Waktu Mulai</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Durasi (Jam)</label>
                        <input type="number" name="duration" id="duration" class="form-control" min="1" max="8" required oninput="calculateTotal()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Harga</label>
                        <input type="text" id="totalPrice" class="form-control" value="Rp 0" readonly>
                        <input type="hidden" name="total_price" id="totalPriceHidden" value="0">
                        <input type="hidden" name="price_per_hour" id="pricePerHourHidden" value="0">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-check me-2"></i> Buat Reservasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="dashboard-card mt-4">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Reservasi Saya</h5>
            <form action="{{ route('customer.reservasi.destroyAll') }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus SEMUA reservasi kamu? Tindakan ini tidak dapat dibatalkan.');">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-trash-alt me-1"></i> Hapus Semua
                </button>
            </form>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>ID</th>
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
                            <td>{{ $reservation->console_type }}</td>
                            <td>{{ \Carbon\Carbon::parse($reservation->date)->format('d M Y') }}</td>
                            <td>{{ $reservation->start_time }}</td>
                            <td>{{ $reservation->duration }} jam</td>
                            <td><strong>Rp {{ number_format($reservation->total_price) }}</strong></td>
                            <td><span class="status-badge status-{{ $reservation->status }}">{{ ucfirst($reservation->status) }}</span></td>
                            <td>
                                @if($reservation->status === 'pending')
                                <form action="{{ route('customer.reservasi.cancel', $reservation->id) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan reservasi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Batal</button>
                                </form>
                                @endif
                            </td>
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
    function calculateTotal() {
        const consoleSelect = document.getElementById('console_type');
        const selectedOption = consoleSelect.options[consoleSelect.selectedIndex];
        const price = Number(selectedOption.dataset.price || 0);
        const duration = Number(document.getElementById('duration').value || 0);
        const total = duration * price;
        document.getElementById('totalPrice').value = `Rp ${total.toLocaleString()}`;
        document.getElementById('totalPriceHidden').value = total;
        document.getElementById('pricePerHourHidden').value = price;
    }
</script>
