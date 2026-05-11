<aside class="sidebar" @if($type === 'admin') style="background: #1a1a2e;" @endif>
    <div class="sidebar-brand">
        <i class="fas fa-gamepad"></i>
        <h5>PS Rent Station</h5>
        @if($type === 'admin')
            <span class="badge bg-danger ms-2">Admin</span>
        @endif
    </div>

    <ul class="sidebar-nav">
        @if($type === 'admin')
            <li class="nav-label">Manajemen</li>
            <li><a href="{{ route('admin.dashboard') }}" class="{{ $active === 'dashboard' ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a></li>
            <li><a href="{{ route('admin.customers') }}" class="{{ $active === 'customers' ? 'active' : '' }}">
                <i class="fas fa-users"></i> Manajemen Customer
            </a></li>
            <li><a href="{{ route('admin.consoles') }}" class="{{ $active === 'consoles' ? 'active' : '' }}">
                <i class="fas fa-desktop"></i> Kelola Console
            </a></li>
            <li><a href="{{ route('admin.reservasi') }}" class="{{ $active === 'reservasi' ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i> Manajemen Reservasi
            </a></li>
            <li><a href="{{ route('admin.antrian') }}" class="{{ $active === 'antrian' ? 'active' : '' }}">
                <i class="fas fa-list-ol"></i> Sistem Antrian
            </a></li>
            <li><a href="{{ route('admin.pembayaran') }}" class="{{ $active === 'pembayaran' ? 'active' : '' }}">
                <i class="fas fa-credit-card"></i> Pembayaran
            </a></li>
            <li><a href="{{ route('admin.payment.settings') }}" class="{{ $active === 'payment_settings' ? 'active' : '' }}">
                <i class="fas fa-cog"></i> Pengaturan Pembayaran
            </a></li>
            <li class="nav-label">Makanan & Minuman</li>
            <li><a href="{{ route('admin.makanan') }}" class="{{ $active === 'makanan' ? 'active' : '' }}">
                <i class="fas fa-utensils"></i> Kelola Stok Makanan
            </a></li>
            <li class="nav-label">Lainnya</li>
            <li><a href="{{ route('admin.keluhan') }}" class="{{ $active === 'keluhan' ? 'active' : '' }}">
                <i class="fas fa-comment-dots"></i> Keluhan Customer
            </a></li>
        @else
            <li class="nav-label">Menu Utama</li>
            <li><a href="{{ route('customer.dashboard') }}" class="{{ $active === 'dashboard' ? 'active' : '' }}">
                <i class="fas fa-home"></i> Dashboard
            </a></li>
            <li><a href="{{ route('customer.reservasi') }}" class="{{ $active === 'reservasi' ? 'active' : '' }}">
                <i class="fas fa-calendar-check"></i> Reservasi
            </a></li>
            <li><a href="{{ route('customer.makanan') }}" class="{{ $active === 'makanan' ? 'active' : '' }}">
                <i class="fas fa-utensils"></i> Pesan Makanan
            </a></li>
            <li><a href="{{ route('customer.pembayaran') }}" class="{{ $active === 'pembayaran' ? 'active' : '' }}">
                <i class="fas fa-credit-card"></i> Pembayaran
            </a></li>
            <li class="nav-label">Lainnya</li>
            <li><a href="{{ route('customer.profile') }}" class="{{ $active === 'profile' ? 'active' : '' }}">
                <i class="fas fa-user-circle"></i> Profil Saya
            </a></li>
            <li><a href="{{ route('customer.keluhan') }}" class="{{ $active === 'keluhan' ? 'active' : '' }}">
                <i class="fas fa-comment-dots"></i> Keluhan
            </a></li>
        @endif
    </ul>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                @if(isset(Auth::user()->name))
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                @else
                    US
                @endif
            </div>
            <div class="user-details">
                <h6>{{ Auth::user()->name ?? 'Pengguna' }}</h6>
                <small>{{ $type === 'admin' ? 'Administrator' : 'Customer' }}</small>
            </div>
        </div>
    </div>
</aside>
