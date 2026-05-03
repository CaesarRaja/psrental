@extends('layouts.app')

@section('title', 'Kelola Stok Makanan - Admin')

@section('sidebar')
    @include('partials.sidebar', ['type' => 'admin', 'active' => 'makanan'])
@endsection

@section('header')
    <div class="main-header">
        <div>
            <h2>Kelola Stok Makanan 🍔</h2>
            <p class="text-muted mb-0">Tambah, edit, dan kelola stok makanan & minuman</p>
        </div>
        <button class="btn-submit" data-bs-toggle="modal" data-bs-target="#addFoodModal">
            <i class="fas fa-plus me-2"></i> Tambah Makanan
        </button>
    </div>
@endsection

@section('content')
    <div class="dashboard-card">
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Emoji</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($foods ?? [] as $food)
                        <tr>
                            <td style="font-size: 1.5rem;">{{ $food->emoji }}</td>
                            <td><strong>{{ $food->name }}</strong></td>
                            <td>{{ $food->category }}</td>
                            <td>Rp {{ number_format($food->price) }}</td>
                            <td>{{ $food->stock }}</td>
                            <td><span class="status-badge status-{{ $food->stock > 0 ? 'available' : 'cancelled' }}">{{ $food->stock > 0 ? 'Tersedia' : 'Habis' }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada data makanan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="dashboard-card mt-4">
        <div class="card-header-custom">
            <h5><i class="fas fa-shopping-bag me-2"></i>Pesanan Makanan Customer</h5>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($foodOrders ?? [] as $order)
                        <tr>
                            <td><strong>#{{ $order->id }}</strong></td>
                            <td>{{ $order->customer->name ?? '-' }}</td>
                            <td>{{ $order->items }}</td>
                            <td><strong>Rp {{ number_format($order->total) }}</strong></td>
                            <td><span class="status-badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada pesanan makanan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
