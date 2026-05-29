@extends('layouts.app')

@section('title', 'Kelola Stok Makanan - Admin')

@section('sidebar')
    @include('partials.sidebar', ['type' => 'admin', 'active' => 'makanan'])
@endsection

@section('header')
    <div>
        <h2>Kelola Stok Makanan</h2>
        <p class="text-muted mb-0">Tambah, edit, dan kelola stok makanan & minuman</p>
    </div>
@endsection

@push('header-actions')
    <button class="btn-submit" data-bs-toggle="modal" data-bs-target="#addFoodModal">
        <i class="fas fa-plus me-2"></i> Tambah Makanan
    </button>
@endpush

@section('content')
    <div class="dashboard-card">
        <div class="card-header-custom header-wrap">
            <h5 class="mb-0"><i class="fas fa-utensils me-2"></i>Kelola Stok Makanan</h5>
            <button class="btn-submit btn-block-mobile" data-bs-toggle="modal" data-bs-target="#addFoodModal">
                <i class="fas fa-plus me-2"></i> Tambah Makanan
            </button>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="table-custom table-card-on-mobile">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($foods ?? [] as $food)
                        <tr>
                            <td data-label="Foto">
                                @if($food->photo)
                                    <img src="{{ asset('storage/' . $food->photo) }}" alt="{{ $food->name }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                @else
                                    <div style="width: 60px; height: 60px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-utensils" style="font-size: 1.5rem; color: #94a3b8;"></i>
                                    </div>
                                @endif
                            </td>
                            <td data-label="Nama"><strong>{{ $food->name }}</strong></td>
                            <td data-label="Kategori">{{ $food->category }}</td>
                            <td data-label="Harga">Rp {{ number_format($food->price) }}</td>
                            <td data-label="Stok">{{ $food->stock }}</td>
                            <td data-label="Status">
                                @if($food->status === 'available' && $food->stock > 0)
                                    <span class="status-badge status-available">Tersedia</span>
                                @else
                                    <span class="status-badge status-cancelled">{{ $food->stock <= 0 ? 'Habis' : 'Tidak Tersedia' }}</span>
                                @endif
                            </td>
                            <td data-label="Aksi">
                                <div class="d-flex flex-wrap gap-1">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editFoodModal{{ $food->id }}">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" action="{{ route('admin.makanan.destroy', $food->id) }}" class="d-inline" onsubmit="return confirm('Hapus makanan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada data makanan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="dashboard-card mt-4">
        <div class="card-header-custom header-wrap">
            <h5 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Pesanan Makanan Customer</h5>
            <form action="{{ route('admin.makanan.orders.destroyAll') }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus SEMUA pesanan makanan? Tindakan ini tidak dapat dibatalkan.');">
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
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($foodOrders ?? [] as $order)
                        <tr>
                            <td data-label="ID"><strong>#{{ $order->id }}</strong></td>
                            <td data-label="Customer">{{ $order->customer->name ?? '-' }}</td>
                            <td data-label="Items">
                                @foreach($order->items as $item)
                                    <div>{{ $item['name'] ?? 'Item' }} x{{ $item['qty'] ?? 1 }}</div>
                                @endforeach
                            </td>
                            <td data-label="Total"><strong>Rp {{ number_format($order->total) }}</strong></td>
                            <td data-label="Status">
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
                            <td data-label="Tanggal">{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y H:i') }}</td>
                            <td data-label="Aksi">
                                <div class="d-flex flex-wrap gap-1">
                                    @if($order->status === 'pending')
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
                                    @elseif($order->status === 'approved')
                                        <form method="POST" action="{{ route('admin.makanan.order.update', $order->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="preparing">
                                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-fire"></i> Proses</button>
                                        </form>
                                    @elseif($order->status === 'preparing')
                                        <form method="POST" action="{{ route('admin.makanan.order.update', $order->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="delivered">
                                            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check-double"></i> Selesai</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada pesanan makanan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Food Modal -->
    <div class="modal fade" id="addFoodModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Makanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.makanan.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-3">
                        <div class="mb-3">
                            <label class="form-label">Foto Makanan</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="Makanan">Makanan</option>
                                <option value="Minuman">Minuman</option>
                                <option value="Snack">Snack</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harga <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control" required min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stok <span class="text-danger">*</span></label>
                            <input type="number" name="stock" class="form-control" required min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="available">Tersedia</option>
                                <option value="unavailable">Tidak Tersedia</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer d-flex flex-column flex-sm-row">
                        <button type="button" class="btn btn-secondary w-100 w-sm-auto" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-submit w-100 w-sm-auto">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach($foods ?? [] as $food)
    <!-- Edit Food Modal -->
    <div class="modal fade" id="editFoodModal{{ $food->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit {{ $food->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.makanan.update', $food->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-3">
                        <div class="mb-3">
                            <label class="form-label">Foto Saat Ini</label>
                            <div>
                                @if($food->photo)
                                    <img src="{{ asset('storage/' . $food->photo) }}" alt="{{ $food->name }}" style="max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 8px;">
                                @else
                                    <div style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-utensils" style="font-size: 1.5rem; color: #94a3b8;"></i>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ganti Foto</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $food->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="Makanan" {{ $food->category === 'Makanan' ? 'selected' : '' }}>Makanan</option>
                                <option value="Minuman" {{ $food->category === 'Minuman' ? 'selected' : '' }}>Minuman</option>
                                <option value="Snack" {{ $food->category === 'Snack' ? 'selected' : '' }}>Snack</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harga <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control" value="{{ $food->price }}" required min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stok <span class="text-danger">*</span></label>
                            <input type="number" name="stock" class="form-control" value="{{ $food->stock }}" required min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="available" {{ $food->status === 'available' ? 'selected' : '' }}>Tersedia</option>
                                <option value="unavailable" {{ $food->status === 'unavailable' ? 'selected' : '' }}>Tidak Tersedia</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer d-flex flex-column flex-sm-row">
                        <button type="button" class="btn btn-secondary w-100 w-sm-auto" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-submit w-100 w-sm-auto">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
@endsection
