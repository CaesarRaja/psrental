<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PS Rent Station</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="auth-container">
        <!-- Left Panel -->
        <div class="auth-panel auth-panel-left">
            <div class="auth-panel-content">
                <div class="brand-section">
                    <i class="fas fa-gamepad brand-icon-large"></i>
                    <h1>PS Rent Station</h1>
                    <p>Selamat datang kembali, Gamer!</p>
                </div>
                <div class="auth-illustration">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/03/PlayStation_5_and_DualSense_with_transparent_background.png/1200px-PlayStation_5_and_DualSense_with_transparent_background.png" alt="PS5">
                </div>
                <div class="auth-features">
                    <div class="auth-feature-item">
                        <i class="fas fa-gamepad"></i>
                        <span>20+ Console Tersedia</span>
                    </div>
                    <div class="auth-feature-item">
                        <i class="fas fa-star"></i>
                        <span>500+ Koleksi Game</span>
                    </div>
                    <div class="auth-feature-item">
                        <i class="fas fa-users"></i>
                        <span>1000+ Customer Puas</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="auth-panel auth-panel-right">
            <div class="auth-form-wrapper">
                <a href="{{ url('/') }}" class="back-home">
                    <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                </a>
                <div class="auth-form-header">
                    <h2>Masuk ke Akun</h2>
                    <p>Masuk untuk memulai petualangan gaming kamu</p>
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

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="auth-form" id="loginForm">
                    @csrf
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
                            <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required>
                            <button type="button" class="toggle-password" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="remember" id="remember">
                            <label class="form-check-label" for="remember">Ingat saya</label>
                        </div>
                        <a href="#" class="forgot-link">Lupa password?</a>
                    </div>

                    <button type="submit" class="btn btn-auth btn-primary btn-block">
                        <i class="fas fa-sign-in-alt me-2"></i> Masuk
                    </button>
                </form>

                <div class="auth-divider">
                    <span>atau masuk dengan</span>
                </div>

                <div class="social-login">
                    <a href="#" class="btn-social btn-google">
                        <i class="fab fa-google"></i> Google
                    </a>
                    <a href="#" class="btn-social btn-facebook">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </a>
                </div>

                <div class="auth-footer">
                    <p>Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
    </script>
</body>
</html>