@php
$user = Auth::user();
$notifications = \App\Models\Notification::query()
    ->forRecipient($user)
    ->latest()
    ->take(20)
    ->get();
$unreadCount = $notifications->where('is_read', false)->count();
@endphp

<div class="notification-wrapper">
    <button class="notification-btn notification-toggle" type="button">
        <i class="fas fa-bell"></i>
        @if($unreadCount > 0)
            <span class="notification-badge">{{ $unreadCount }}</span>
        @endif
    </button>

    <div class="notification-dropdown">
        <div class="notification-header">
            <h6 class="mb-0">Notifikasi</h6>
            <div class="d-flex align-items-center gap-2">
                @if($unreadCount > 0)
                    <button class="notification-mark-all mark-all-read">
                        <small>Tandai semua dibaca</small>
                    </button>
                @endif
                <button type="button" class="notification-close" aria-label="Tutup">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="notification-body notification-list">
            @forelse($notifications as $notification)
                <div class="notification-item {{ $notification->is_read ? 'read' : 'unread' }}" data-id="{{ $notification->id }}" data-link="{{ $notification->link }}">
                    <div class="notification-content">
                        <div class="notification-title">{{ $notification->title }}</div>
                        <div class="notification-message">{{ $notification->message }}</div>
                        <div class="notification-time">{{ $notification->created_at->diffForHumans() }}</div>
                    </div>
                    @if(!$notification->is_read)
                        <span class="notification-dot"></span>
                    @endif
                </div>
            @empty
                <div class="notification-empty">
                    <i class="fas fa-bell-slash"></i>
                    <p>Belum ada notifikasi</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
