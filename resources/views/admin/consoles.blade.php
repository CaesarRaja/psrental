@extends('layouts.app')

@section('title', 'Kelola Console - Admin')

@section('sidebar')
    @include('partials.sidebar', ['type' => 'admin', 'active' => 'consoles'])
@endsection

@section('header')
    <div class="main-header">
        <div>
            <h2>Kelola Console</h2>
            <p class="text-muted mb-0">Tambah, edit, dan kelola console & harga sewa</p>
        </div>
        <div class="header-actions">
            @include('partials.notifications')
            <button class="btn-submit" data-bs-toggle="modal" data-bs-target="#addConsoleModal">
                <i class="fas fa-plus me-2"></i> Tambah Console
            </button>
            <a href="{{ route('logout') }}" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <h6>Total Console</h6>
                <h3>{{ $totalConsoles ?? 0 }}</h3>
            </div>
            <div class="stat-icon icon-primary">
                <i class="fas fa-desktop"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h6>Tersedia</h6>
                <h3>{{ $availableConsoles ?? 0 }}</h3>
            </div>
            <div class="stat-icon icon-success">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h6>Sedang Dipakai</h6>
                <h3>{{ $busyConsoles ?? 0 }}</h3>
            </div>
            <div class="stat-icon icon-warning">
                <i class="fas fa-gamepad"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h6>Maintenance</h6>
                <h3>{{ $maintenanceConsoles ?? 0 }}</h3>
            </div>
            <div class="stat-icon icon-danger">
                <i class="fas fa-tools"></i>
            </div>
        </div>
    </div>

    <div class="dashboard-card mt-3">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Pengaturan Harga per Tipe</h5>
        </div>
        <div class="card-body-custom">
            <div class="row g-3">
                @foreach(['PS4', 'PS5', 'VR'] as $type)
                <div class="col-md-4">
                    <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                        <div>
                            <h6 class="mb-1">{{ $type }}</h6>
                            <p class="mb-0 text-muted">Rp {{ number_format($typePrices[$type] ?? 0) }}/jam</p>
                        </div>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editPriceModal{{ $type }}">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    @foreach($consoleByType ?? [] as $type => $counts)
    <div class="dashboard-card mt-3">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-gamepad me-2"></i>{{ $type }}</h5>
            <div>
                <span class="badge bg-success me-1">{{ $counts['available'] }} Tersedia</span>
                <span class="badge bg-warning me-1">{{ $counts['busy'] }} Sibuk</span>
                <span class="badge bg-danger">{{ $counts['maintenance'] }} Maintenance</span>
            </div>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Nama Console</th>
                            <th>Tipe</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consoles->where('type', $type) as $console)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="console-dot {{ $console->status }}"></span>
                                    <strong>{{ $console->name }}</strong>
                                </div>
                            </td>
                            <td>{{ $console->type }}</td>
                            <td>
                                @switch($console->status)
                                    @case('available')
                                        <span class="status-badge status-approved">Tersedia</span>
                                        @break
                                    @case('busy')
                                        <span class="status-badge status-preparing">Sedang Dipakai</span>
                                        @break
                                    @case('maintenance')
                                        <span class="status-badge status-rejected">Maintenance</span>
                                        @break
                                    @default
                                        <span class="status-badge">{{ ucfirst($console->status) }}</span>
                                @endswitch
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editConsoleModal{{ $console->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.consoles.destroy', $console->id) }}" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus console ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Belum ada console {{ $type }}.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Add Console Modal -->
    <div class="modal fade" id="addConsoleModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Console</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.consoles.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Console <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: PS5-1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipe <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="PS4">PS4</option>
                                <option value="PS5">PS5</option>
                                <option value="VR">VR</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="available">Tersedia</option>
                                <option value="busy">Sedang Dipakai</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="padding: 12px 32px; font-weight: 600;">Batal</button>
                        <button type="submit" class="btn-submit">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach($consoles ?? [] as $console)
    <!-- Edit Console Modal -->
    <div class="modal fade" id="editConsoleModal{{ $console->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Console</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.consoles.update', $console->id) }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Console <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $console->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipe <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="PS4" {{ $console->type === 'PS4' ? 'selected' : '' }}>PS4</option>
                                <option value="PS5" {{ $console->type === 'PS5' ? 'selected' : '' }}>PS5</option>
                                <option value="VR" {{ $console->type === 'VR' ? 'selected' : '' }}>VR</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="available" {{ $console->status === 'available' ? 'selected' : '' }}>Tersedia</option>
                                <option value="busy" {{ $console->status === 'busy' ? 'selected' : '' }}>Sedang Dipakai</option>
                                <option value="maintenance" {{ $console->status === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="padding: 12px 32px; font-weight: 600;">Batal</button>
                        <button type="submit" class="btn-submit">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    @foreach(['PS4', 'PS5', 'VR'] as $type)
    <!-- Edit Price Modal -->
    <div class="modal fade" id="editPriceModal{{ $type }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Harga {{ $type }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.consoles.updateTypePrice') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Harga per Jam (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="price_per_hour" class="form-control" value="{{ $typePrices[$type] ?? 0 }}" min="0" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="padding: 12px 32px; font-weight: 600;">Batal</button>
                        <button type="submit" class="btn-submit">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
@endsection
