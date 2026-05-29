@extends('layouts.app')

@section('title', 'Keluhan - PS Rent Station')

@section('sidebar')
    @include('partials.sidebar', ['type' => 'customer', 'active' => 'keluhan'])
@endsection

@section('header')
    <div class="main-header">
        <div>
            <h2>Keluhan & Umpan Balik</h2>
            <p class="text-muted mb-0">Sampaikan keluhan atau masukan agar layanan kami lebih baik</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="dashboard-card mb-4">
        <div class="card-header-custom">
            <h5><i class="fas fa-comment-dots me-2"></i>Kirim Keluhan</h5>
        </div>
        <div class="card-body-custom">
            <form action="{{ route('customer.keluhan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label">Kategori</label>
                        <select name="category" class="form-select" required>
                            <option value="">Pilih kategori...</option>
                            <option value="console">Console</option>
                            <option value="ruangan">Ruangan</option>
                            <option value="pelayanan">Pelayanan</option>
                            <option value="makanan">Makanan</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Prioritas</label>
                        <select name="priority" class="form-select" required>
                            <option value="">Pilih prioritas...</option>
                            <option value="low">Rendah</option>
                            <option value="medium">Sedang</option>
                            <option value="high">Tinggi</option>
                            <option value="urgent">Mendesak</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Judul Keluhan</label>
                    <input type="text" name="subject" class="form-control" placeholder="Judul singkat keluhan" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="message" class="form-control" rows="4" placeholder="Jelaskan masalah Anda" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Foto / Lampiran</label>
                    <input type="file" name="attachment" class="form-control" accept="image/*">
                    <small class="text-muted">Format: JPG, PNG, GIF. Maks 2MB.</small>
                </div>
                <button type="submit" class="btn-submit btn-block-mobile">
                    <i class="fas fa-paper-plane me-2"></i>Kirim Keluhan
                </button>
            </form>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="card-header-custom header-wrap">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Keluhan</h5>
            <form action="{{ route('customer.keluhan.destroyAll') }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus SEMUA keluhan kamu? Tindakan ini tidak dapat dibatalkan.');">
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
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Prioritas</th>
                            <th>Status</th>
                            <th>Respons</th>
                            <th>Foto</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($complaints ?? [] as $complaint)
                        <tr>
                            <td data-label="ID"><strong>#{{ $complaint->id }}</strong></td>
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
                            <td data-label="Respons">{{ $complaint->response ?? '-' }}</td>
                            <td data-label="Foto">
                                @if($complaint->attachment)
                                    <a href="{{ asset('storage/' . $complaint->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-image"></i> Lihat
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td data-label="Tanggal">{{ \Carbon\Carbon::parse($complaint->created_at)->format('d M Y') }}</td>
                            <td data-label="Aksi">
                                <span class="text-muted">-</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Belum ada keluhan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
