@extends('layouts.app')

@section('title', 'Manajemen Reservasi - Admin')

@section('sidebar')
    @include('partials.sidebar', ['type' => 'admin', 'active' => 'reservasi'])
@endsection

@section('header')
    <div class="main-header">
        <div>
            <h2>Manajemen Reservasi</h2>
            <p class="text-muted mb-0">Kelola semua reservasi customer</p>
        </div>
        @include('partials.header-actions-auth')
    </div>
@endsection

@section('content')
    <div class="dashboard-card mb-4">
        <div class="card-body-custom">
            <form action="{{ route('admin.reservasi') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Console</label>
                    <select name="console" class="form-select">
                        <option value="">Semua Console</option>
                        <option value="PS4">PS4</option>
                        <option value="PS5">PS5</option>
                        <option value="VR">VR</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn-submit w-100">
                        <i class="fas fa-search me-2"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Reservasi</h5>
                <span class="badge bg-primary">{{ $reservations->total() ?? 0 }} Total</span>
            </div>
            <form action="{{ route('admin.reservasi.destroyAll') }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus SEMUA reservasi? Tindakan ini tidak dapat dibatalkan.');">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-trash-alt me-1"></i> Hapus Semua
                </button>
            </form>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Console</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Durasi</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservations ?? [] as $reservation)
                        <tr>
                            <td><strong>#{{ $reservation->id }}</strong></td>
                            <td>{{ $reservation->customer->name ?? '-' }}</td>
                            <td>{{ $reservation->console_type }}</td>
                            <td>{{ \Carbon\Carbon::parse($reservation->date)->format('d M Y') }}</td>
                            <td>{{ $reservation->start_time }}</td>
                            <td>{{ $reservation->duration }} jam</td>
                            <td><strong>Rp {{ number_format($reservation->total_price) }}</strong></td>
                            <td><span class="status-badge status-{{ $reservation->status }}">{{ ucfirst($reservation->status) }}</span></td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @if($reservation->status === 'pending')
                                        <form action="{{ route('admin.reservasi.approve', $reservation->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                        <form action="{{ route('admin.reservasi.reject', $reservation->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger">Tolak</button>
                                        </form>
                                    @elseif($reservation->status === 'approved')
                                        @php
                                            $slots = (int) ($availableByType[$reservation->console_type] ?? 0);
                                        @endphp
                                        @if($slots > 0)
                                        <form action="{{ route('admin.reservasi.start', $reservation->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary">Mulai</button>
                                        </form>
                                        @else
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="showAppNotify('Mohon maaf, console penuh. Semua unit {{ $reservation->console_type }} sedang digunakan.')">Mulai</button>
                                        @endif
                                    @elseif($reservation->status === 'active')
                                        <form action="{{ route('admin.reservasi.complete', $reservation->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Selesai</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Tidak ada data reservasi</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $reservations->links() ?? '' }}
    </div>
@endsection
