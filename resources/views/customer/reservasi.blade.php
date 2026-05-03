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
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="dashboard-card h-100">
                <div class="card-body-custom text-center">
                    <div style="font-size: 3rem; margin-bottom: 16px;">🎮</div>
                    <h5>PlayStation 4</h5>
                    <p class="text-muted">Rp 15.000/jam</p>
                    <button type="button" class="btn btn-pricing w-100" onclick="selectConsole('PS4', 15000)">Pilih PS4</button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card h-100" style="border-color: var(--primary);">
                <div class="card-body-custom text-center">
                    <div style="font-size: 3rem; margin-bottom: 16px;">🕹️</div>
                    <h5>PlayStation 5</h5>
                    <p class="text-muted">Rp 25.000/jam</p>
                    <button type="button" class="btn btn-pricing w-100" onclick="selectConsole('PS5', 25000)">Pilih PS5</button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card h-100">
                <div class="card-body-custom text-center">
                    <div style="font-size: 3rem; margin-bottom: 16px;">🥽</div>
                    <h5>PlayStation VR</h5>
                    <p class="text-muted">Rp 35.000/jam</p>
                    <button type="button" class="btn btn-pricing w-100" onclick="selectConsole('VR', 35000)">Pilih VR</button>
                </div>
            </div>
        </div>
    </div>

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
                        <input type="text" id="selectedConsole" class="form-control" placeholder="Pilih console di atas" readonly required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Reservasi</label>
                        <input type="date" name="date" class="form-control" required min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Waktu Mulai</label>
                        <select name="start_time" class="form-select" required>
                            <option value="">Pilih waktu</option>
                            <option value="10:00">10:00</option>
                            <option value="12:00">12:00</option>
                            <option value="14:00">14:00</option>
                            <option value="16:00">16:00</option>
                            <option value="18:00">18:00</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Durasi (Jam)</label>
                        <select name="duration" id="duration" class="form-select" required onchange="calculateTotal()">
                            <option value="">Pilih durasi</option>
                            <option value="1">1 Jam</option>
                            <option value="2">2 Jam</option>
                            <option value="3">3 Jam</option>
                            <option value="4">4 Jam</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Harga</label>
                        <input type="text" id="totalPrice" class="form-control" value="Rp 0" readonly>
                        <input type="hidden" name="total_price" id="totalPriceHidden" value="0">
                        <input type="hidden" name="console_type" id="consoleTypeHidden" value="">
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
@endsection

@push('scripts')
<script>
    function selectConsole(type, price) {
        document.getElementById('selectedConsole').value = type;
        document.getElementById('consoleTypeHidden').value = type;
        document.getElementById('pricePerHourHidden').value = price;
        calculateTotal();
    }

    function calculateTotal() {
        const duration = Number(document.getElementById('duration').value || 0);
        const price = Number(document.getElementById('pricePerHourHidden').value || 0);
        const total = duration * price;
        document.getElementById('totalPrice').value = `Rp ${total.toLocaleString()}`;
        document.getElementById('totalPriceHidden').value = total;
    }
</script>
