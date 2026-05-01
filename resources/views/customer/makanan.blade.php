<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Makanan - PS Rent Station</title>
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
                <li><a href="{{ route('customer.makanan') }}" class="active">
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
                    <h2>Pesan Makanan & Minuman 🍔</h2>
                    <p class="text-muted mb-0">Nikmati makanan favorit sambil gaming!</p>
                </div>
            </div>

            <!-- Food Menu -->
            <div class="row g-4 mb-4">
                @forelse($foods ?? [] as $food)
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="food-card">
                        <div class="food-card-img">
                            {{ $food->emoji ?? '🍔' }}
                        </div>
                        <div class="food-card-body">
                            <h6>{{ $food->name }}</h6>
                            <div class="food-price">Rp {{ number_format($food->price) }}</div>
                            <div class="food-stock">
                                <i class="fas fa-box me-1"></i>
                                Stok: <strong>{{ $food->stock }}</strong> tersedia
                            </div>
                            <button class="btn-add-food" onclick="addToCart({{ $food->id }}, '{{ $food->name }}', {{ $food->price }})"
                                    {{ $food->stock <= 0 ? 'disabled' : '' }}>
                                <i class="fas fa-cart-plus me-1"></i>
                                {{ $food->stock <= 0 ? 'Habis' : 'Tambah' }}
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center text-muted py-5">
                    <i class="fas fa-utensils fa-3x mb-3 d-block"></i>
                    <h5>Menu makanan belum tersedia</h5>
                </div>
                @endforelse
            </div>

            <!-- Cart Section -->
            <div class="dashboard-card" id="cartSection" style="display: none;">
                <div class="card-header-custom">
                    <h5><i class="fas fa-shopping-cart me-2"></i>Keranjang Pesanan</h5>
                    <button class="btn btn-sm btn-outline-danger" onclick="clearCart()">
                        <i class="fas fa-trash"></i> Kosongkan
                    </button>
                </div>
                <div class="card-body-custom">
                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Makanan</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="cartItems">
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td colspan="2"><strong id="cartTotal">Rp 0</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="mt-3">
                        <form action="{{ route('customer.makanan.order') }}" method="POST" id="orderForm">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Catatan (Opsional)</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Pedas level 5, tanpa sayur"></textarea>
                            </div>
                            <button type="submit" class="btn-submit">
                                <i class="fas fa-paper-plane me-2"></i> Pesan Sekarang
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Order History -->
            <div class="dashboard-card mt-4">
                <div class="card-header-custom">
                    <h5><i class="fas fa-receipt me-2"></i>Riwayat Pesanan</h5>
                </div>
                <div class="card-body-custom p-0">
                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders ?? [] as $order)
                                <tr>
                                    <td><strong>#{{ $order->id }}</strong></td>
                                    <td>{{ $order->items }}</td>
                                    <td><strong>Rp {{ number_format($order->total) }}</strong></td>
                                    <td>
                                        <span class="status-badge status-{{ $order->status }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Belum ada pesanan
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
        let cart = [];

        function addToCart(id, name, price) {
            const existingItem = cart.find(item => item.id === id);
            if (existingItem) {
                existingItem.qty++;
            } else {
                cart.push({ id, name, price, qty: 1 });
            }
            updateCart();
            document.getElementById('cartSection').style.display = 'block';
        }

        function removeFromCart(id) {
            cart = cart.filter(item => item.id !== id);
            updateCart();
            if (cart.length === 0) {
                document.getElementById('cartSection').style.display = 'none';
            }
        }

        function clearCart() {
            cart = [];
            updateCart();
            document.getElementById('cartSection').style.display = 'none';
        }

        function updateCart() {
            const tbody = document.getElementById('cartItems');
            let html = '';
            let total = 0;

            cart.forEach(item => {
                const subtotal = item.price * item.qty;
                total += subtotal;
                html += `
                    <tr>
                        <td>${item.name}</td>
                        <td>Rp ${item.price.toLocaleString()}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changeQty(${item.id}, -1)">-</button>
                                <span>${item.qty}</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changeQty(${item.id}, 1)">+</button>
                            </div>
                        </td>
                        <td>Rp ${subtotal.toLocaleString()}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${item.id})">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
            document.getElementById('cartTotal').textContent = 'Rp ' + total.toLocaleString();
        }

        function changeQty(id, change) {
            const item = cart.find(item => item.id === id);
            if (item) {
                item.qty += change;
                if (item.qty <= 0) {
                    removeFromCart(id);
                    return;
                }
            }
            updateCart();
        }
    </script>
</body>
</html>