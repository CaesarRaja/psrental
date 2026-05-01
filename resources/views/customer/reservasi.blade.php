<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi - PS Rent Station</title>
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
                <li><a href="{{ route('customer.reservasi') }}" class="active">
                    <i class="fas fa-calendar-check"></i> Reservasi
                </a></li>
                <li><a href="{{ route('customer.makanan') }}">
                    <i class="fas fa-utensils"></i> Pesan Makanan
                </a></li>
                <li><a href="{{ route('customer.pembayaran') }}">
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
                    <h2>Reservasi PlayStation 🎮</h2>
                    <p class="text-muted mb-0">Pilih console dan buat reservasi kamu</p>
                </div>
                <div class="header-actions">
                    <a href="{{ route('customer.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            <!-- Console Selection -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="dashboard-card h-100">
                        <div class="card-body-custom text-center">
                            <div style="font-size: 3rem; margin-bottom: 16px;">🎮</div>
                            <h5>PlayStation 4</h5>
                            <p class="text-muted">Rp 15.000/jam</p>
                            <div class="console-status-grid mb-3">
                                <div class="console-status-item available">
                                    <span class="console-dot"></span>
                                    <h6>PS4-1</h6>
                                    <small>Tersedia</small>
                                </div>
                                <div class="console-status-item available">
                                    <span class="console-dot"></span>
                                    <h6>PS4-2</h6>
                                    <small>Tersedia</small>
                                </div>
                                <div class="console-status-item booked">
                                    <span class="console-dot"></span>
                                    <h6>PS4-3</h6>
                                    <small>Dipakai</small>
                                </div>
                            </div>
                            <button class="btn btn-pricing w-100" onclick="selectConsole('PS4', 15000)">Pilih PS4</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="dashboard-card h-100" style="border-color: var(--primary);">
                        <div class="card-body-custom text-center">
                            <div style="font-size: 3rem; margin-bottom: 16px;">🕹️</div>
                            <h5>PlayStation 5</h5>
                            <p class="text-muted">Rp 25.000/jam</p>
                            <div class="console-status-grid mb-3">
                                <div class="console-status-item available">
                                    <span class="console-dot"></span>
                                    <h6>PS5-1</h6>
                                    <small>Tersedia</small>
                                </div>
                                <div class="console-status-item available">
                                    <span class="console-dot"></span>
                                    <h6>PS5-2</h6>
                                    <small>Tersedia</small>
                                </div>
                                <div class="console-status-item booked">
                                    <span class="console-dot"></span>
                                    <h6>PS5-3</h6>
                                    <small>Tersedia</small>
                                </div>
                            </div>
                            <button class="btn btn-pricing w-100" onclick="selectConsole('PS5', 25000)">Pilih PS5</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="dashboard-card h-100">
                        <div class="card-body-custom text-center">
                            <div style="font-size: 3rem; margin-bottom: 16px;">🥽</div>
                            <h5>PlayStation VR</h5>
                            <p class="text-muted">Rp 35.000/jam</p>
                            <div class="console-status-grid mb-3">
                                <div class="console-status-item available">
                                    <span class="console-dot"></span>
                                    <h6>VR-1</h6>
                                    <small>Tersedia</small>
                                </div>
                                <div class="console-status-item available">
                                    <span class="console-dot"></span>
                                    <h6>VR-2</h6>
                                    <small>Tersedia</small>
                                </div>
                            </div>
                            <button class="btn btn-pricing w-100" onclick="selectConsole('VR', 35000)">Pilih VR</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reservation Form -->
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
                                    <option value="11:00">11:00</option>
                                    <option value="12:00">12:00</option>
                                    <option value="13:00">13:00</option>
                                    <option value="14:00">14:00</option>
                                    <option value="15:00">15:00</option>
                                    <option value="16:00">16:00</option>
                                    <option value="17:00">17:00</option>
                                    <option value="18:00">18:00</option>
                                    <option value="19:00">19:00</option>
                                    <option value="20:00">20:00</option>
                                    <option value="21:00">21:00</option>
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
                                    <option value="5">5 Jam</option>
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

            <!-- Reservation History -->
            <div class="dashboard-card mt-4">
                <div class="card-header-custom">
                    <h5><i class="fas fa-history me-2"></i>Riwayat Reservasi</h5>
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
                                    <td>
                                        <span class="status-badge status-{{ $reservation->status }}">
                                            {{ ucfirst($reservation->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($reservation->status === 'pending')
                                        <form action="{{ route('customer.reservasi.cancel', $reservation->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Belum ada riwayat reservasi
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
    <script>
        let currentPrice = 0;

        function selectConsole(type, price) {
            document.getElementById('selectedConsole').value = type;
            document.getElementById('consoleTypeHidden').value = type;
            document.getElementById('pricePerHourHidden').value = price;
            currentPrice = price;
            calculateTotal();
        }

        function calculateTotal() {
            const duration = document.getElementById('duration').value;
            const total = currentPrice * duration;
            document.getElementById('totalPrice').value = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('totalPriceHidden').value = total;
        }
    </script>
</body>
</html>