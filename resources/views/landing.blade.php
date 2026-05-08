@extends('layouts.guest')

@section('title', 'PS Rent Station - Rental PlayStation Terbaik')

@section('content')
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                <i class="fas fa-gamepad me-2 brand-icon"></i>
                <span class="fw-bold">PS Rent Station</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link active" href="#beranda">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#fitur">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#harga">Harga</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a href="{{ route('login') }}" class="btn btn-outline-login">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a href="{{ route('register') }}" class="btn btn-register">
                            <i class="fas fa-user-plus me-1"></i> Register
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section" id="beranda">
        <div class="hero-particles" id="particles"></div>
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6 hero-content" data-aos="fade-right">
                    <span class="hero-badge">
                        <i class="fas fa-bolt me-1"></i> #1 Rental PlayStation di Kota
                    </span>
                    <h1 class="hero-title">
                        Mainkan Game<br>
                        <span class="text-gradient">Favoritmu</span><br>
                        Tanpa Batas!
                    </h1>
                    <p class="hero-subtitle">
                        Rental PlayStation terbaik dengan console terbaru, koleksi game terlengkap,
                        dan pengalaman gaming yang luar biasa. Reservasi sekarang dan dapatkan tempat terbaik!
                    </p>
                    <div class="hero-buttons d-flex flex-wrap gap-3">
                        <a href="{{ route('register') }}" class="btn btn-hero-primary">
                            <i class="fas fa-calendar-check me-2"></i> Reservasi Sekarang
                        </a>
                        <a href="#harga" class="btn btn-hero-secondary">
                            <i class="fas fa-tags me-2"></i> Lihat Harga
                        </a>
                    </div>
                    <div class="hero-stats mt-5">
                        <div class="stat-item">
                            <h3 class="stat-number" data-count="500">500+</h3>
                            <p class="stat-label">Koleksi Game</p>
                        </div>
                        <div class="stat-item">
                            <h3 class="stat-number" data-count="20">20+</h3>
                            <p class="stat-label">Console Tersedia</p>
                        </div>
                        <div class="stat-item">
                            <h3 class="stat-number" data-count="1000">1000+</h3>
                            <p class="stat-label">Customer Puas</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 hero-image" data-aos="fade-left">
                    <div class="ps-console-wrapper">
                        <div class="console-glow"></div>
                        <div class="console-image">
                            <img src="{{ asset('images/logo/stikps.png') }}" alt="PS5 Console">
                        </div>
                        <div class="floating-card card-1">
                            <i class="fas fa-star"></i>
                            <span>Rating 4.9</span>
                        </div>
                        <div class="floating-card card-2">
                            <i class="fas fa-users"></i>
                            <span>200+ Player Online</span>
                        </div>
                        <div class="floating-card card-3">
                            <i class="fas fa-trophy"></i>
                            <span>1000+ Games</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-scroll-indicator">
            <a href="#fitur">
                <span class="scroll-mouse">
                    <span class="scroll-wheel"></span>
                </span>
            </a>
        </div>
    </section>

    <section class="features-section" id="fitur">
        <div class="container">
            <div class="section-header text-center" data-aos="fade-up">
                <span class="section-badge">Keunggulan Kami</span>
                <h2 class="section-title">Kenapa Pilih <span class="text-gradient">Kami?</span></h2>
                <p class="section-subtitle">Pengalaman gaming terbaik dengan fasilitas premium</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-gamepad"></i>
                        </div>
                        <h4>Console Terbaru</h4>
                        <p>PS4 Pro, PS5, dan PlayStation VR dengan performa terbaik dan selalu terawat.</p>
                        <div class="feature-line"></div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h4>Reservasi Online</h4>
                        <p>Pesan tempat dari rumah tanpa perlu antri. Sistem reservasi real-time yang mudah.</p>
                        <div class="feature-line"></div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h4>Sistem Antrian</h4>
                        <p>Sistem antrian digital yang efisien. Pantau posisi antrianmu secara real-time.</p>
                        <div class="feature-line"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="pricing-section" id="harga">
        <div class="container">
            <div class="section-header text-center" data-aos="fade-up">
                <span class="section-badge">Pilihan Paket</span>
                <h2 class="section-title">Paket <span class="text-gradient">Harga</span></h2>
                <p class="section-subtitle">Pilih console sesuai kebutuhan dan budget kamu</p>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="pricing-card">
                        <div class="pricing-header">
                            <h4>PlayStation 4</h4>
                            <div class="price">
                                <span class="currency">Rp</span>
                                <span class="amount">15K</span>
                                <span class="period">/jam</span>
                            </div>
                        </div>
                        <ul class="pricing-features">
                            <li><i class="fas fa-check"></i> TV 43 inch Full HD</li>
                            <li><i class="fas fa-check"></i> 2 Controller</li>
                            <li><i class="fas fa-check"></i> 200+ Game</li>
                            <li><i class="fas fa-check"></i> Ruang Ber-AC</li>
                            <li><i class="fas fa-check"></i> Free WiFi</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-pricing">Pilih PS4</a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="pricing-card popular">
                        <div class="popular-badge">
                            <span>POPULER</span>
                        </div>
                        <div class="pricing-header">
                            <h4>PlayStation 5</h4>
                            <div class="price">
                                <span class="currency">Rp</span>
                                <span class="amount">25K</span>
                                <span class="period">/jam</span>
                            </div>
                        </div>
                        <ul class="pricing-features">
                            <li><i class="fas fa-check"></i> TV 55 inch 4K HDR</li>
                            <li><i class="fas fa-check"></i> 2 DualSense Controller</li>
                            <li><i class="fas fa-check"></i> 300+ Game</li>
                            <li><i class="fas fa-check"></i> Ruang VIP Ber-AC</li>
                            <li><i class="fas fa-check"></i> Free Snack & Drink</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-pricing">Pilih PS5</a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="pricing-card">
                        <div class="pricing-header">
                            <h4>PlayStation VR</h4>
                            <div class="price">
                                <span class="currency">Rp</span>
                                <span class="amount">35K</span>
                                <span class="period">/jam</span>
                            </div>
                        </div>
                        <ul class="pricing-features">
                            <li><i class="fas fa-check"></i> PS5 + VR2 Headset</li>
                            <li><i class="fas fa-check"></i> 1 VR + 1 Controller</li>
                            <li><i class="fas fa-check"></i> 50+ VR Games</li>
                            <li><i class="fas fa-check"></i> Ruang Khusus VR</li>
                            <li><i class="fas fa-check"></i> Free Snack & Drink</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-pricing">Pilih VR</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="testimonial-section">
        <div class="container">
            <div class="section-header text-center" data-aos="fade-up">
                <span class="section-badge">Testimoni</span>
                <h2 class="section-title">Apa Kata <span class="text-gradient">Mereka?</span></h2>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="testimonial-card">
                        <div class="testimonial-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"Tempat rental PS terbaik yang pernah saya kunjungi! Console bersih, game lengkap, dan pelayanan ramah."</p>
                        <div class="testimonial-author">
                            <div class="author-avatar"><span>AR</span></div>
                            <div class="author-info">
                                <h6>Ahmad Rizky</h6>
                                <small>Gamer</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="testimonial-card">
                        <div class="testimonial-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"Sistem reservasi online sangat memudahkan. Bisa pesan dari rumah dan langsung main begitu sampai."</p>
                        <div class="testimonial-author">
                            <div class="author-avatar"><span>DP</span></div>
                            <div class="author-info">
                                <h6>Dina Putri</h6>
                                <small>Streamer</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="testimonial-card">
                        <div class="testimonial-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <p class="testimonial-text">"Harga terjangkau, fasilitas premium. VR experience-nya luar biasa! Pasti bakal balik lagi."</p>
                        <div class="testimonial-author">
                            <div class="author-avatar"><span>BS</span></div>
                            <div class="author-info">
                                <h6>Budi Santoso</h6>
                                <small>Content Creator</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container">
            <div class="cta-content text-center" data-aos="zoom-in">
                <h2>Siap Bermain Game Favoritmu?</h2>
                <p>Daftar sekarang dan dapatkan pengalaman gaming terbaik!</p>
                <a href="{{ route('register') }}" class="btn btn-hero-primary btn-lg">
                    <i class="fas fa-rocket me-2"></i> Mulai Sekarang
                </a>
            </div>
        </div>
    </section>

    <footer class="footer-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="footer-brand">
                        <a href="{{ url('/') }}" class="d-flex align-items-center text-decoration-none">
                            <i class="fas fa-gamepad me-2 brand-icon"></i>
                            <span class="fw-bold">PS Rent Station</span>
                        </a>
                        <p class="mt-3">Tempat rental PlayStation terbaik di kota. Nikmati pengalaman gaming premium dengan console terbaru dan koleksi game terlengkap.</p>
                        <div class="footer-social">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-facebook"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-tiktok"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5 class="footer-title">Menu</h5>
                    <ul class="footer-links">
                        <li><a href="#beranda">Beranda</a></li>
                        <li><a href="#fitur">Fitur</a></li>
                        <li><a href="#harga">Harga</a></li>
                        <li><a href="{{ route('login') }}">Login</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">Jam Operasional</h5>
                    <ul class="footer-links">
                        <li><span>Senin - Jumat: 10:00 - 22:00</span></li>
                        <li><span>Sabtu - Minggu: 09:00 - 23:00</span></li>
                        <li><span>Hari Libur: 09:00 - 00:00</span></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">Kontak</h5>
                    <ul class="footer-links">
                        <li><i class="fas fa-map-marker-alt me-2"></i> Bukittinggi</li>
                        <li><i class="fas fa-phone me-2"></i> 0812-3456-7890</li>
                        <li><i class="fas fa-envelope me-2"></i> info@psrentstation.com</li>
                    </ul>
                </div>
            </div>
            <hr class="footer-divider">
            <div class="footer-bottom text-center">
                <p class="mb-0">&copy; {{ date('Y') }} PS Rent Station. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <a href="#" class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </a>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof AOS !== 'undefined') {
            AOS.init({ duration: 800, once: true });
        }
    });
</script>
@endpush
