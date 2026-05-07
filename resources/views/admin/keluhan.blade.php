@extends('layouts.app')

@section('title', 'Keluhan Customer - Admin')

@section('sidebar')
    @include('partials.sidebar', ['type' => 'admin', 'active' => 'keluhan'])
@endsection

@section('header')
    <div class="main-header">
        <div>
            <h2>Keluhan Customer</h2>
            <p class="text-muted mb-0">Lihat dan respon keluhan pelanggan</p>
        </div>
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
        <div class="card-header-custom">
            <h5><i class="fas fa-comment-dots me-2"></i>Daftar Keluhan</h5>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="table-custom">
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
                            <td><strong>#{{ $complaint->id }}</strong></td>
                            <td>{{ $complaint->customer->name ?? '-' }}</td>
                            <td>{{ $complaint->subject }}</td>
                            <td>{{ ucfirst($complaint->category) }}</td>
                            <td>
                                <span class="status-badge status-{{ $complaint->priority }}">
                                    {{ ucfirst($complaint->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-{{ $complaint->status }}">
                                    {{ ucfirst($complaint->status) }}
                                </span>
                            </td>
                            <td>
                                @if($complaint->attachment)
                                    <a href="{{ asset('storage/' . $complaint->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-image"></i> Lihat
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#responseModal{{ $complaint->id }}">
                                    <i class="fas fa-reply me-1"></i> Respon
                                </button>
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
<div class="modal fade" id="responseModal{{ $complaint->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Respon Keluhan #{{ $complaint->id }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.keluhan.response', $complaint->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Customer</label>
                        <p>{{ $complaint->customer->name ?? '-' }}</p>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Kategori</label>
                            <p>{{ ucfirst($complaint->category) }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Prioritas</label>
                            <p>{{ ucfirst($complaint->priority) }}</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Judul</label>
                        <p>{{ $complaint->subject }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi</label>
                        <p class="p-3 bg-light rounded">{{ $complaint->message }}</p>
                    </div>
                    @if($complaint->attachment)
                    <div class="mb-3">
                        <label class="form-label fw-bold">Foto Lampiran</label>
                        <div>
                            <img src="{{ asset('storage/' . $complaint->attachment) }}" alt="Attachment" class="img-fluid rounded" style="max-height: 300px;">
                        </div>
                    </div>
                    @endif
                    <hr>
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
            </form>
        </div>
    </div>
</div>
@endforeach
