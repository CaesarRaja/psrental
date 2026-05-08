@extends('layouts.auth')

@section('title', 'Register - PS Rent Station')

@section('content')
    <div class="auth-container auth-container-register">
        <div class="auth-panel auth-panel-right order-lg-1 order-2">
            <div class="auth-form-wrapper">
                <a href="{{ url('/') }}" class="back-home">
                    <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                </a>
                <div class="auth-form-header">
                    <h2>Buat Akun Baru</h2>
                    <p>Bergabung dengan komunitas gamer kami</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('register.post') }}" method="POST" class="auth-form" id="registerForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label for="name">Nama Lengkap</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" name="name" id="name" class="form-control" placeholder="Nama kamu" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label for="phone">No. Telepon</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-phone input-icon"></i>
                                    <input type="tel" name="phone" id="phone" class="form-control" placeholder="08xxxxxxxxxx" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group-custom">
                        <label for="email">Email</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" id="email" class="form-control" placeholder="Masukkan email kamu" required>
                        </div>
                    </div>

                    <div class="form-group-custom">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 8 karakter" required>
                            <button type="button" class="toggle-password" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength">
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <small id="strengthText"></small>
                        </div>
                    </div>

                    <div class="form-group-custom">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ulangi password" required>
                        </div>
                    </div>

                    <div class="form-group-custom">
                        <label for="address">Alamat (Opsional)</label>
                        <div class="input-wrapper">
                            <i class="fas fa-map-marker-alt input-icon"></i>
                            <textarea name="address" id="address" class="form-control" rows="2" placeholder="Alamat kamu"></textarea>
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="terms" id="terms" required>
                        <label class="form-check-label" for="terms">
                            Saya setuju dengan <a href="#">Syarat & Ketentuan</a>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-auth btn-primary btn-block">
                        <i class="fas fa-user-plus me-2"></i> Daftar Sekarang
                    </button>
                </form>

                <div class="auth-footer">
                    <p>Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a></p>
                </div>
            </div>
        </div>

        <div class="auth-panel auth-panel-left order-lg-2 order-1">
            <div class="auth-panel-content">
                <div class="brand-section">
                    <i class="fas fa-gamepad brand-icon-large"></i>
                    <h1>PS Rent Station</h1>
                    <p>Bergabunglah dengan ribuan gamer lainnya!</p>
                </div>
                <div class="auth-illustration">
                    <img src="{{ asset('images/logo/stikps.png') }}" alt="PS5">
                </div>
                <div class="auth-benefits">
                    <h5>Keuntungan Member:</h5>
                    <div class="auth-feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Reservasi online 24/7</span>
                    </div>
                    <div class="auth-feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Diskon spesial member</span>
                    </div>
                    <div class="auth-feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Pesan makanan & minuman</span>
                    </div>
                    <div class="auth-feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Sistem antrian digital</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function togglePassword(id) {
        const input = document.getElementById(id);
        const icon = input.nextElementSibling.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('password');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');

        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                if (password.length >= 8) strength++;
                if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
                if (/\d/.test(password)) strength++;
                if (/[^a-zA-Z\d]/.test(password)) strength++;

                const colors = ['#dc3545', '#fd7e14', '#ffc107', '#28a745'];
                const labels = ['Lemah', 'Sedang', 'Kuat', 'Sangat Kuat'];
                strengthFill.style.width = (strength * 25) + '%';
                strengthFill.style.backgroundColor = colors[Math.max(0, strength - 1)];
                strengthText.textContent = labels[Math.max(0, strength - 1)] || '';
            });
        }
    });
</script>
@endpush
