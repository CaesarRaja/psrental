@extends('layouts.app')

@section('title', 'Sistem Antrian - Admin')

@section('sidebar')
    @include('partials.sidebar', ['type' => 'admin', 'active' => 'antrian'])
@endsection

@section('header')
    <div class="main-header">
        <div>
            <h2>Sistem Antrian</h2>
            <p class="text-muted mb-0">Kelola antrian customer dengan cepat</p>
        </div>
        <form action="{{ route('admin.antrian.add') }}" method="POST" class="d-flex gap-2">
            @csrf
            <button type="submit" class="btn-submit">
                <i class="fas fa-plus me-2"></i> Tambah Antrian
            </button>
        </form>
    </div>
@endsection

@section('content')
    <div class="dashboard-card mb-4">
        <div class="card-header-custom">
            <h5><i class="fas fa-list-ol me-2"></i>Antrian Saat Ini</h5>
            <form action="{{ route('admin.antrian.reset') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger">Reset</button>
            </form>
        </div>
        <div class="card-body-custom">
            <div class="queue-list">
                @forelse($queues ?? [] as $queue)
                <div class="queue-item">
                    <div>
                        <h5>{{ $queue->number }}</h5>
                        <span>{{ $queue->customer_name ?? 'Tidak dikenal' }}</span>
                    </div>
                    <form action="{{ route('admin.antrian.call', $queue->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary">Panggil</button>
                    </form>
                </div>
                @empty
                <p class="text-muted">Tidak ada antrian saat ini.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
