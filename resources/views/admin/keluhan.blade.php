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
                            <th>Status</th>
                            <th>Respons</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($complaints ?? [] as $complaint)
                        <tr>
                            <td><strong>#{{ $complaint->id }}</strong></td>
                            <td>{{ $complaint->customer->name ?? '-' }}</td>
                            <td>{{ $complaint->title }}</td>
                            <td><span class="status-badge status-{{ $complaint->status }}">{{ ucfirst($complaint->status) }}</span></td>
                            <td>{{ $complaint->response ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($complaint->created_at)->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada keluhan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
