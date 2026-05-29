@extends('layouts.app')

@section('title', 'Pengaturan Pembayaran - Admin')

@section('sidebar')
    @include('partials.sidebar', ['type' => 'admin', 'active' => 'payment_settings'])
@endsection

@section('header')
    <div>
        <h2>Pengaturan Pembayaran</h2>
        <p class="text-muted mb-0">Atur QRIS dan informasi rekening bank</p>
    </div>
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-qrcode me-2"></i>QRIS</h5>
                </div>
                <div class="card-body-custom">
                    @if($settings && $settings->qris_image)
                        <div class="mb-3 text-center">
                            <img src="{{ asset('storage/' . $settings->qris_image) }}" alt="QRIS" class="img-fluid rounded" style="max-height: 300px;">
                        </div>
                    @else
                        <div class="text-center text-muted mb-3 py-4">
                            <i class="fas fa-qrcode fa-3x mb-2"></i>
                            <p class="mb-0">Belum ada gambar QRIS</p>
                        </div>
                    @endif
                    <form action="{{ route('admin.payment.settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Upload Gambar QRIS</label>
                            <input type="file" name="qris_image" class="form-control" accept="image/*">
                            <small class="text-muted">Format JPG, PNG. Maks 2MB.</small>
                        </div>
                        <button type="submit" class="btn-submit w-100">
                            <i class="fas fa-save me-2"></i>Simpan QRIS
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-university me-2"></i>Informasi Rekening</h5>
                </div>
                <div class="card-body-custom">
                    <form action="{{ route('admin.payment.settings.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nama Bank</label>
                            <input type="text" name="bank_name" class="form-control" placeholder="Contoh: BCA, BRI, Mandiri"
                                value="{{ $settings->bank_name ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor Rekening</label>
                            <input type="text" name="account_number" class="form-control" placeholder="Contoh: 1234567890"
                                value="{{ $settings->account_number ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Atas Nama</label>
                            <input type="text" name="account_holder" class="form-control" placeholder="Contoh: PS Rent Station"
                                value="{{ $settings->account_holder ?? '' }}">
                        </div>
                        <button type="submit" class="btn-submit w-100">
                            <i class="fas fa-save me-2"></i>Simpan Rekening
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
