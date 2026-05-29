@extends('layouts.app')

@section('title', 'Sistem Antrian - Admin')

@section('sidebar')
    @include('partials.sidebar', ['type' => 'admin', 'active' => 'antrian'])
@endsection

@section('header')
    <div>
        <h2>Sistem Antrian</h2>
        <p class="text-muted mb-0">Kelola antrian customer dengan cepat</p>
    </div>
@endsection

@push('header-actions')
    <div class="d-flex gap-2">
    <form action="{{ route('admin.antrian.next') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn-submit">
            <i class="fas fa-forward me-2"></i> Next
        </button>
    </form>
    <form action="{{ route('admin.antrian.reset') }}" method="POST" class="d-inline" onsubmit="return confirm('Reset semua antrian?')">
        @csrf
        <button type="submit" class="btn-submit" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
            <i class="fas fa-trash-alt me-2"></i> Reset
        </button>
    </form>
    </div>
@endpush

@section('content')
    {{-- Stats Bar --}}
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-info">
                <h6>Antrian Menunggu</h6>
                <h3>{{ $queues->count() }}</h3>
            </div>
            <div class="stat-icon icon-warning">
                <i class="fas fa-hourglass-half"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h6>Sedang Main</h6>
                <h3>{{ $currentServing ? 1 : 0 }}</h3>
            </div>
            <div class="stat-icon icon-success">
                <i class="fas fa-gamepad"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h6>Selesai Hari Ini</h6>
                <h3>{{ $todayCompleted }}</h3>
            </div>
            <div class="stat-icon icon-purple">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>

    @if($currentServing)
    <div class="dashboard-card mb-4" style="border: none;">
        <div class="now-serving">
            <div class="serving-label"><i class="fas fa-bullhorn me-2"></i>Sedang Dipanggil</div>
            <div class="serving-number">{{ $currentServing->queue_number }}</div>
            <div class="serving-name">{{ $currentServing->customer->name ?? 'Tidak dikenal' }}</div>
            <div class="serving-console">
                <i class="fas fa-tv"></i> {{ $currentServing->console_type }}
            </div>
        </div>
    </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="dashboard-card mb-4">
                <div class="card-header-custom">
                    <h5><i class="fas fa-list-ol me-2"></i>Antrian Menunggu</h5>
                    <span class="status-badge status-pending">{{ $queues->count() }} orang</span>
                </div>
                <div class="card-body-custom" style="padding: 16px 20px;">
                    @forelse($queues as $queue)
                    <div class="queue-item">
                        <div class="queue-info">
                            <div class="queue-badge">{{ $queue->queue_number }}</div>
                            <div class="queue-details">
                                <h6>{{ $queue->customer->name ?? 'Tidak dikenal' }}</h6>
                                <div class="queue-console">
                                    <i class="fas fa-tv"></i> {{ $queue->console_type }}
                                </div>
                            </div>
                        </div>
                        <form action="{{ route('admin.antrian.call', $queue->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-bullhorn me-1"></i> Panggil
                            </button>
                        </form>
                    </div>
                    @empty
                    <div class="queue-empty">
                        <i class="fas fa-clipboard-list"></i>
                        <p>Tidak ada antrian saat ini.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dashboard-card mb-4">
                <div class="card-header-custom">
                    <h5><i class="fas fa-plus me-2"></i>Tambah Antrian</h5>
                </div>
                <div class="card-body-custom">
                    <form action="{{ route('admin.antrian.add') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Customer</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">Pilih customer...</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipe Console</label>
                            <select name="console_type" class="form-select" required>
                                <option value="">Pilih...</option>
                                <option value="PS4">PS4</option>
                                <option value="PS5">PS5</option>
                                <option value="VR">VR</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-submit w-100">
                            <i class="fas fa-plus me-1"></i> Tambah ke Antrian
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
