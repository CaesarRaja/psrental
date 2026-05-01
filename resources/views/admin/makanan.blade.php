<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Stok Makanan - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar" style="background: #1a1a2e;">
            <div class="sidebar-brand">
                <i class="fas fa-gamepad"></i>
                <h5>PS Rent Station</h5>
                <span class="badge bg-danger ms-2">Admin</span>
            </div>
            <ul class="sidebar-nav">
                <li class="nav-label">Manajemen</li>
                <li><a href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a></li>
                <li><a href="{{ route('admin.reservasi') }}">
                    <i class="fas fa-calendar-alt"></i> Manajemen Reservasi
                </a></li>
                <li><a href="{{ route('admin.antrian') }}">
                    <i class="fas fa-list-ol"></i> Sistem Antrian
                </a></li>
                <li><a href="{{ route('admin.pembayaran') }}">
                    <i class="fas fa-credit-card"></i> Pembayaran
                </a></li>
                <li class="nav-label">Makanan & Minuman</li>
                <li><a href="{{ route('admin.makanan') }}" class="active">
                    <i class="fas fa-utensils"></i> Kelola Stok Makanan
                </a></li>
                <li class="nav-label">Lainnya</li>
                <li><a href="{{ route('admin.keluhan') }}">
                    <i class="fas fa-comment-dots"></i> Keluhan Customer
                </a></li>
            </ul>
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar" style="background: linear-gradient(135deg, #ef4444, #dc2626);">AD</div>
                    <div class="user-details">
                        <h6>{{ Auth::user()->name }}</h6>
                        <small style="color: #ef4444;">Administrator</small>
                    </div>
                </div>
            </div>
        </aside>

        <main class="main-content">
            <div class="main-header">
                <div>
                    <h2>Kelola Stok Makanan 🍔</h2>
                    <p class="text-muted mb-0">Tambah, edit, dan kelola stok makanan & minuman</p>
                </div>
                <button class="btn-submit" data-bs-toggle="modal" data-bs-target="#addFoodModal">
                    <i class="fas fa-plus me-2"></i> Tambah Makanan
                </button>
            </div>

            <!-- Food Management Table -->
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
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($foods ?? [] as $food)
                                <tr>
                                    <td style="font-size: 1.5rem;">{{ $food->emoji }}</td>
                                    <td><strong>{{ $food->name }}</strong></td>
                                    <td>{{ $food->category }}</td>
                                    <td>Rp {{ number_format($food->price) }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    onclick="updateStock({{ $food->id }}, -1)">-</button>
                                            <span class="fw-bold">{{ $food->stock }}</span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    onclick="updateStock({{ $food->id }}, 1)">+</button>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $food->stock > 0 ? 'available' : 'cancelled' }}">
                                            {{ $food->stock > 0 ? 'Tersedia' : 'Habis' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal" data-bs-target="#editFoodModal{{ $food->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.makanan.destroy', $food->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Belum ada data makanan
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Food Orders Management -->
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
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($foodOrders ?? [] as $order)
                                <tr>
                                    <td><strong>#{{ $order->id }}</strong></td>
                                    <td>{{ $order->customer->name }}</td>
                                    <td>
                                        @php
                                            $items = json_decode($order->items);
                                            $itemNames = [];
                                            foreach($items as $item) {
                                                $food = App\Models\Food::find($item->id);
                                                $itemNames[] = ($food ? $food->emoji : '🍔') . ' x' . $item->qty;
                                            }
                                        @endphp
                                        {{ implode(', ', $itemNames) }}
                                    </td>
                                    <td><strong>Rp {{ number_format($order->total) }}</strong></td>
                                    <td>
                                        <span class="status-badge status-{{ $order->status === 'delivered' ? 'completed' : ($order->status === 'preparing' ? 'pending' : 'available') }}">
                                            @if($order->status === 'pending')
                                                Baru
                                            @elseif($order->status === 'preparing')
                                                Disiapkan
                                            @elseif($order->status === 'delivered')
                                                Dikirim
                                            @else
                                                {{ ucfirst($order->status) }}
                                            @endif
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y H:i') }}</td>
                                    <td>
                                        @if($order->status === 'pending')
                                        <form action="{{ route('admin.makanan.order.update', $order->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="preparing">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-fire"></i> Siapkan
                                            </button>
                                        </form>
                                        @elseif($order->status === 'preparing')
                                        <form action="{{ route('admin.makanan.order.update', $order->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="delivered">
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-check"></i> Kirim
                                            </button>
                                        </form>
                                        @else
                                        <span class="text-muted">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Belum ada pesanan makanan
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

    <!-- Add Food Modal -->
    <div class="modal fade" id="addFoodModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                <div class="modal-header" style="border-color: var(--border-color);">
                    <h5 class="modal-title">Tambah Makanan Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.makanan.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Emoji</label>
                            <input type="text" name="emoji" class="form-control" placeholder="🍔" required style="background: #1e293b; border-color: #334155; color: white;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" class="form-control" placeholder="Nama makanan" required style="background: #1e293b; border-color: #334155; color: white;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="category" class="form-select" required style="background: #1e293b; border-color: #334155; color: white;">
                                <option value="Makanan">Makanan</option>
                                <option value="Minuman">Minuman</option>
                                <option value="Snack">Snack</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harga</label>
                            <input type="number" name="price" class="form-control" placeholder="15000" required style="background: #1e293b; border-color: #334155; color: white;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stok Awal</label>
                            <input type="number" name="stock" class="form-control" placeholder="50" required style="background: #1e293b; border-color: #334155; color: white;">
                        </div>
                    </div>
                    <div class="modal-footer" style="border-color: var(--border-color);">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-submit">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
    <script>
        function updateStock(id, change) {
            fetch(`/admin/makanan/${id}/stock`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ change: change })
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      location.reload();
                  }
              });
        }
    </script>
</body>
</html>