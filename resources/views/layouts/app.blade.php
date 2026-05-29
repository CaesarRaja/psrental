<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PS Rent Station')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <div class="dashboard-wrapper">
        @yield('sidebar')

        <!-- Sidebar overlay for mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <main class="main-content">
            <!-- Mobile header bar -->
            <div class="mobile-header-bar" id="mobileHeader">
                <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Toggle menu">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="header-actions-mobile">
                    @auth
                        <a href="{{ route('logout') }}" class="mobile-logout-btn" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                        @include('partials.notifications')
                    @endauth
                </div>
            </div>

            @yield('header')
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3 mb-0" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            @endif
            @if (session('console_full'))
            <div class="app-notify app-notify--warning" role="alert">
                <div class="app-notify__icon-wrap" aria-hidden="true">
                    <i class="fas fa-gamepad"></i>
                </div>
                <div class="app-notify__body">
                    <p class="app-notify__msg">{{ session('console_full') }}</p>
                    @auth
                        @if (auth()->user()->isCustomer())
                            <a href="{{ route('customer.dashboard') }}" class="app-notify__cta">
                                <i class="fas fa-clipboard-list me-1"></i> Lihat status antrian di dashboard
                            </a>
                        @endif
                    @endauth
                </div>
                <button type="button" class="app-notify__close" data-app-notify-close aria-label="Tutup">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            @endif
            @yield('content')
        </main>
    </div>

    @auth
        @php
            $currentUser = auth()->user();
            $isCustomer = $currentUser->isCustomer();
        @endphp
        <!-- Bottom Navigation -->
        <nav class="bottom-nav" id="bottomNav">
            <ul class="bottom-nav-items">
                @if($isCustomer)
                    <li>
                        <a href="{{ route('customer.dashboard') }}" class="bottom-nav-item {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('customer.reservasi') }}" class="bottom-nav-item {{ request()->routeIs('customer.reservasi*') ? 'active' : '' }}">
                            <i class="fas fa-calendar-check"></i>
                            <span>Reservasi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('customer.makanan') }}" class="bottom-nav-item {{ request()->routeIs('customer.makanan*') ? 'active' : '' }}">
                            <i class="fas fa-utensils"></i>
                            <span>Makanan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('customer.pembayaran') }}" class="bottom-nav-item {{ request()->routeIs('customer.pembayaran*') ? 'active' : '' }}">
                            <i class="fas fa-credit-card"></i>
                            <span>Bayar</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('customer.profile') }}" class="bottom-nav-item {{ request()->routeIs('customer.profile*') ? 'active' : '' }}">
                            <i class="fas fa-user"></i>
                            <span>Profil</span>
                        </a>
                    </li>
                @else
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="bottom-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reservasi') }}" class="bottom-nav-item {{ request()->routeIs('admin.reservasi*') ? 'active' : '' }}">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Reservasi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.antrian') }}" class="bottom-nav-item {{ request()->routeIs('admin.antrian*') ? 'active' : '' }}">
                            <i class="fas fa-list-ol"></i>
                            <span>Antrian</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.pembayaran') }}" class="bottom-nav-item {{ request()->routeIs('admin.pembayaran*') ? 'active' : '' }}">
                            <i class="fas fa-credit-card"></i>
                            <span>Bayar</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.makanan') }}" class="bottom-nav-item {{ request()->routeIs('admin.makanan*') ? 'active' : '' }}">
                            <i class="fas fa-utensils"></i>
                            <span>Makanan</span>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>

        @include('partials.chat-widget')
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
    @stack('scripts')

    <div class="toast-container" id="toastContainer">
        @if(session('success'))
            <x-toast-message type="success" :message="session('success')" />
        @endif
        @if(session('error'))
            <x-toast-message type="error" :message="session('error')" />
        @endif
        @if(session('info'))
            <x-toast-message type="info" :message="session('info')" />
        @endif
        @if(session('warning'))
            <x-toast-message type="warning" :message="session('warning')" />
        @endif
    </div>

    <script>
        // Auto-dismiss toast messages after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const toasts = document.querySelectorAll('.toast-message');
            toasts.forEach(function(toast) {
                setTimeout(function() {
                    toast.style.animation = 'slideIn 0.3s ease-out reverse';
                    setTimeout(function() {
                        toast.remove();
                    }, 300);
                }, 3000);
            });
        });
    </script>
    @auth
    <script>
        // Heartbeat: update last_seen_at every 60 seconds to track online status
        (function() {
            function sendHeartbeat() {
                fetch('{{ route("chat.heartbeat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                }).catch(function() {});
            }
            sendHeartbeat(); // Immediately on page load
            setInterval(sendHeartbeat, 60000); // Every 60 seconds
        })();
    </script>
    @endauth
</body>
</html>
