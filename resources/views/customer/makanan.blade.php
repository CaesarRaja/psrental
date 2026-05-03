@extends('layouts.app')

@section('title', 'Pesan Makanan - PS Rent Station')

@section('sidebar')
    @include('partials.sidebar', ['type' => 'customer', 'active' => 'makanan'])
@endsection

@section('header')
    <div class="main-header">
        <div>
            <h2>Pesan Makanan & Minuman 🍔</h2>
            <p class="text-muted mb-0">Nikmati makanan favorit sambil gaming!</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="row g-4 mb-4">
        @forelse($foods ?? [] as $food)
        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="food-card">
                <div class="food-card-img">{{ $food->emoji ?? '🍔' }}</div>
                <div class="food-card-body">
                    <h6>{{ $food->name }}</h6>
                    <div class="food-price">Rp {{ number_format($food->price) }}</div>
                    <div class="food-stock">
                        <i class="fas fa-box me-1"></i>
                        Stok: <strong>{{ $food->stock }}</strong>
                    </div>
                    <button type="button" class="btn-add-food" onclick="addToCart({{ $food->id }}, '{{ $food->name }}', {{ $food->price }})" {{ $food->stock <= 0 ? 'disabled' : '' }}>
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
                    <input type="hidden" name="cart_data" id="cartData">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane me-2"></i> Pesan Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
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
        cartSection.style.display = cart.length ? 'block' : 'none';
    }
</script>
