<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PS Rent Station')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <style>
        .toast-message {
            animation: slideIn 0.3s ease-out;
            transition: all 0.3s ease;
        }
        
        .toast-message:hover {
            transform: translateX(-5px);
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="dashboard-wrapper">
        @yield('sidebar')

        <main class="main-content">
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
        @include('partials.chat-widget')
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
    @stack('scripts')
<div class="toast-container">
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
