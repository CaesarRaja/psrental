@extends('layouts.app')

@section('title', 'Pesan Makanan - PS Rent Station')

@section('sidebar')
    @include('partials.sidebar', ['type' => 'customer', 'active' => 'makanan'])
@endsection

@section('header')
    <div>
        <h2>Pesan Makanan & Minuman</h2>
        <p class="text-muted mb-0">Nikmati makanan favorit sambil gaming!</p>
    </div>
@endsection

@section('content')
    <div class="row g-4 mb-4">
        @forelse($foods ?? [] as $food)
        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="food-card">
                <div class="food-card-img">
                    @if($food->photo)
                        <img src="{{ asset('storage/' . $food->photo) }}" alt="{{ $food->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #f1f5f9;">
                            <i class="fas fa-utensils" style="font-size: 2.5rem; color: #94a3b8;"></i>
                        </div>
                    @endif
                </div>
                <div class="food-card-body">
                    <h6>{{ $food->name }}</h6>
                    <div class="food-price">Rp {{ number_format($food->price) }}</div>
                    <div class="food-stock">
                        <i class="fas fa-box me-1"></i>
                        Stok: <strong>{{ $food->stock }}</strong>
                    </div>
                    <button type="button" class="btn-add-food" onclick="addToCart({{ $food->id }}, '{{ $food->name }}', {{ $food->price }})" {{ $food->stock <= 0 || $food->status !== 'available' ? 'disabled' : '' }}>
                        <i class="fas fa-cart-plus me-1"></i>
                        {{ $food->stock <= 0 || $food->status !== 'available' ? 'Habis' : 'Tambah' }}
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

    <div class="dashboard-card" id="cartSection" style="display: none;">
        <div class="card-header-custom">
            <h5><i class="fas fa-shopping-cart me-2"></i>Keranjang Pesanan</h5>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearCart()">
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
                    <tbody id="cartItems"></tbody>
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
                    <input type="hidden" name="items" id="cartData">
                    <input type="hidden" name="total" id="cartTotalInput">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane me-2"></i> Pesan Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if($orders->count() > 0)
    <div class="dashboard-card mt-4">
        <div class="card-header-custom">
            <h5><i class="fas fa-history me-2"></i>Pesanan Saya</h5>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Catatan</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td>
                                @foreach($order->items as $item)
                                    <div>{{ $item['name'] ?? 'Item' }} x{{ $item['qty'] ?? 1 }}</div>
                                @endforeach
                            </td>
                            <td>Rp {{ number_format($order->total) }}</td>
                            <td>{{ $order->notes ?? '-' }}</td>
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
                            <td>
                                @if($order->status === 'pending')
                                    <form method="POST" action="{{ route('customer.makanan.order.cancel', $order->id) }}" class="d-inline" onsubmit="return confirm('Batalkan pesanan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times"></i> Batalkan
                                        </button>
                                    </form>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
@endsection

@push('scripts')
<script>
    let cart = [];

    function addToCart(id, name, price) {
        const existing = cart.find(item => item.id === id);
        if (existing) existing.qty += 1;
        else cart.push({ id, name, price, qty: 1 });
        updateCart();
    }

    function removeFromCart(id) {
        cart = cart.filter(item => item.id !== id);
        updateCart();
    }

    function clearCart() {
        cart = [];
        updateCart();
    }

    function updateCart() {
        const tbody = document.getElementById('cartItems');
        const cartSection = document.getElementById('cartSection');
        let html = '';
        let total = 0;
        cart.forEach(item => {
            const subtotal = item.price * item.qty;
            total += subtotal;
            html += `
                <tr>
                    <td>${item.name}</td>
                    <td>Rp ${item.price.toLocaleString()}</td>
                    <td>${item.qty}</td>
                    <td>Rp ${subtotal.toLocaleString()}</td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${item.id})"><i class="fas fa-times"></i></button></td>
                </tr>`;
        });
        tbody.innerHTML = html;
        document.getElementById('cartTotal').textContent = `Rp ${total.toLocaleString()}`;
        document.getElementById('cartData').value = JSON.stringify(cart);
        document.getElementById('cartTotalInput').value = total;
        cartSection.style.display = cart.length ? 'block' : 'none';
    }
</script>
@endpush
