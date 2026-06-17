@extends('layouts.app')

@section('title', 'Keluhan Customer - Admin')

@section('sidebar')
    @include('partials.sidebar', ['type' => 'admin', 'active' => 'keluhan'])
@endsection

@section('header')
    <div>
        <h2>Keluhan Customer</h2>
        <p class="text-muted mb-0">Lihat dan respon keluhan pelanggan</p>
    </div>
@endsection

@section('content')
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-info">
                <h6>Total Keluhan</h6>
                <h3>{{ $complaints->count() }}</h3>
            </div>
            <div class="stat-icon icon-primary">
                <i class="fas fa-comments"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h6>Belum Ditangani</h6>
                <h3>{{ $openComplaints }}</h3>
            </div>
            <div class="stat-icon icon-warning">
                <i class="fas fa-envelope-open"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h6>Sedang Diproses</h6>
                <h3>{{ $progressComplaints }}</h3>
            </div>
            <div class="stat-icon icon-purple">
                <i class="fas fa-spinner"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h6>Selesai</h6>
                <h3>{{ $resolvedComplaints }}</h3>
            </div>
            <div class="stat-icon icon-success">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="card-header-custom header-wrap">
            <h5 class="mb-0"><i class="fas fa-comment-dots me-2"></i>Daftar Keluhan</h5>
            <form action="{{ route('admin.keluhan.destroyAll') }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus SEMUA keluhan? Tindakan ini tidak dapat dibatalkan.');">
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
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Prioritas</th>
                            <th>Status</th>
                            <th>Foto</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($complaints ?? [] as $complaint)
                        <tr>
                            <td data-label="ID"><strong>{{ $complaint->id }}</strong></td>
                            <td data-label="Customer">{{ $complaint->customer->name ?? '-' }}</td>
                            <td data-label="Judul">{{ $complaint->subject }}</td>
                            <td data-label="Kategori">{{ ucfirst($complaint->category) }}</td>
                            <td data-label="Prioritas">
                                <span class="status-badge status-{{ $complaint->priority }}">
                                    {{ ucfirst($complaint->priority) }}
                                </span>
                            </td>
                            <td data-label="Status">
                                <span class="status-badge status-{{ $complaint->status }}">
                                    {{ ucfirst($complaint->status) }}
                                </span>
                            </td>
                            <td data-label="Foto">
                                @if($complaint->attachment)
                                    <a href="{{ asset('storage/' . $complaint->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-image"></i> Lihat
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td data-label="Aksi">
                                <div class="d-flex flex-wrap gap-1">
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#responseModal{{ $complaint->id }}">
                                        <i class="fas fa-reply me-1"></i> Respon
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Belum ada keluhan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@foreach($complaints as $complaint)
<form action="{{ route('admin.keluhan.response', $complaint->id) }}" method="POST">
    @csrf
    <div class="modal fade" id="responseModal{{ $complaint->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Respon {{ $complaint->id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 p-md-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Customer</label>
                        <p class="mb-0">{{ $complaint->customer->name ?? '-' }}</p>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-bold">Kategori</label>
                            <p class="mb-0">{{ ucfirst($complaint->category) }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-bold">Prioritas</label>
                            <p class="mb-0">{{ ucfirst($complaint->priority) }}</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Judul</label>
                        <p class="mb-0">{{ $complaint->subject }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi</label>
                        <p class="p-3 bg-light rounded mb-0">{{ $complaint->message }}</p>
                    </div>
                    @if($complaint->attachment)
                    <div class="mb-3">
                        <label class="form-label fw-bold">Foto Lampiran</label>
                        <div>
                            <img src="{{ asset('storage/' . $complaint->attachment) }}" alt="Attachment" class="img-fluid rounded" style="max-height: 250px;">
                        </div>
                    </div>
                    @endif
                    <hr class="my-3">
                    <div class="mb-3">
                        <label class="form-label">Respon Admin</label>
                        <textarea name="response" class="form-control" rows="4" placeholder="Tulis respon Anda...">{{ $complaint->response ?? '' }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ubah Status</label>
                        <select name="status" class="form-select">
                            <option value="in_progress" {{ $complaint->status === 'in_progress' ? 'selected' : '' }}>Sedang Diproses</option>
                            <option value="resolved" {{ $complaint->status === 'resolved' ? 'selected' : '' }}>Selesai</option>
                            <option value="closed" {{ $complaint->status === 'closed' ? 'selected' : '' }}>Tutup</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i> Kirim Respon
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endforeach
