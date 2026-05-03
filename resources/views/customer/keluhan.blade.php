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
            <form action="{{ route('customer.keluhan.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Judul Keluhan</label>
                    <input type="text" name="title" class="form-control" placeholder="Judul singkat keluhan" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="message" class="form-control" rows="4" placeholder="Jelaskan masalah Anda" required></textarea>
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane me-2"></i>Kirim Keluhan
                </button>
            </form>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="card-header-custom">
            <h5><i class="fas fa-history me-2"></i>Riwayat Keluhan</h5>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Status</th>
                            <th>Respons</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($complaints ?? [] as $complaint)
                        <tr>
                            <td><strong>#{{ $complaint->id }}</strong></td>
                            <td>{{ $complaint->title }}</td>
                            <td>
                                <span class="status-badge status-{{ $complaint->status }}">
                                    {{ ucfirst($complaint->status) }}
                                </span>
                            </td>
                            <td>{{ $complaint->response ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($complaint->created_at)->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
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
