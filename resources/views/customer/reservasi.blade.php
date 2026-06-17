@extends('layouts.app')

@section('title', 'Reservasi - PS Rent Station')

@section('sidebar')
    @include('partials.sidebar', ['type' => 'customer', 'active' => 'reservasi'])
@endsection

@section('header')
    <div>
        <h2>Reservasi PlayStation 🎮</h2>
        <p class="text-muted mb-0">Pilih console dan buat reservasi kamu</p>
    </div>
@endsection

@push('header-actions')
    <a href="{{ route('customer.dashboard') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
@endpush

@section('content')
    <div class="dashboard-card">
        <div class="card-header-custom">
            <h5><i class="fas fa-tags me-2"></i>Daftar Harga Console</h5>
        </div>
        <div class="card-body-custom">
            <div class="row g-3">
                @foreach($consoleTypes as $ct)
                <div class="col-md-4">
                    <div class="d-flex justify-content-between align-items-center p-3 border rounded bg-light">
                        <div>
                            <h6 class="mb-1">{{ $ct->type }}</h6>
                            <p class="mb-0 text-primary fw-bold">Rp {{ number_format($ct->price_per_hour) }}/jam</p>
                        </div>
                        <i class="fas fa-gamepad text-muted"></i>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="dashboard-card mt-4">
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
                            @foreach($consoleTypes as $ct)
                                <option value="{{ $ct->type }}" data-price="{{ $ct->price_per_hour }}">{{ $ct->type }} – Rp {{ number_format($ct->price_per_hour) }}/jam</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Reservasi</label>
                        <input type="date" name="date" class="form-control" required min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label">Waktu Mulai</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label">Durasi (Jam)</label>
                        <input type="number" name="duration" id="duration" class="form-control" min="1" max="8" required oninput="calculateTotal()">
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label">Total Harga</label>
                        <input type="text" id="totalPrice" class="form-control" value="Rp 0" readonly>
                        <input type="hidden" name="total_price" id="totalPriceHidden" value="0">
                        <input type="hidden" name="price_per_hour" id="pricePerHourHidden" value="0">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn-submit btn-block-mobile">
                        <i class="fas fa-check me-2"></i> Buat Reservasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="dashboard-card mt-4">
        <div class="card-header-custom header-wrap">
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
                <table class="table-custom table-card-on-mobile">
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
                            <td data-label="ID"><strong>{{ $reservation->id }}</strong></td>
                            <td data-label="Console">{{ $reservation->console_type }}</td>
                            <td data-label="Tanggal">{{ \Carbon\Carbon::parse($reservation->date)->format('d M Y') }}</td>
                            <td data-label="Waktu">{{ $reservation->start_time }}</td>
                            <td data-label="Durasi">{{ $reservation->duration }} jam</td>
                            <td data-label="Total"><strong>Rp {{ number_format($reservation->total_price) }}</strong></td>
                            <td data-label="Status"><span class="status-badge status-{{ $reservation->status }}">{{ ucfirst($reservation->status) }}</span></td>
                            <td data-label="Aksi">
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

    <div class="pagination-wrapper mt-4">
        {{ $reservations->links() ?? '' }}
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
