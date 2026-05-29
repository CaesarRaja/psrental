/**
 * PS Rent Station - Dashboard JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // ===== Mobile Sidebar Toggle =====
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function openSidebar() {
        sidebar.classList.add('active');
        if (sidebarOverlay) sidebarOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        var bottomNav = document.getElementById('bottomNav');
        if (bottomNav) bottomNav.style.display = 'none';
    }

    function closeSidebar() {
        sidebar.classList.remove('active');
        if (sidebarOverlay) sidebarOverlay.classList.remove('active');
        document.body.style.overflow = '';
        var bottomNav = document.getElementById('bottomNav');
        if (bottomNav) bottomNav.style.display = '';
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            if (sidebar.classList.contains('active')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    // Close sidebar when clicking overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }

    // Close sidebar with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar && sidebar.classList.contains('active')) {
            closeSidebar();
        }
    });

    // ===== Touch swipe to close sidebar =====
    if (sidebar) {
        let touchStartX = 0;
        let touchEndX = 0;

        sidebar.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        sidebar.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            const swipeDistance = touchStartX - touchEndX;
            // Swipe left more than 80px to close
            if (swipeDistance > 80 && sidebar.classList.contains('active')) {
                closeSidebar();
            }
        }, { passive: true });
    }

    // ===== Close sidebar when clicking a nav link on mobile =====
    if (sidebar) {
        sidebar.querySelectorAll('.sidebar-nav a').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 991) {
                    closeSidebar();
                }
            });
        });
    }

    // Auto-refresh for queue system
    const queueNumber = document.getElementById('currentQueueNumber');
    if (queueNumber) {
        setInterval(() => {
            // Fetch latest queue number from server
            fetch('/admin/antrian/current')
                .then(response => response.json())
                .then(data => {
                    if (data.queue_number) {
                        queueNumber.textContent = data.queue_number;
                    }
                })
                .catch(() => {});
        }, 10000); // Refresh every 10 seconds
    }

    // Confirm before destructive actions
    document.querySelectorAll('form[action*="destroy"], form[action*="cancel"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Apakah kamu yakin ingin melakukan aksi ini?')) {
                e.preventDefault();
            }
        });
    });
});

/**
 * Confirm Action
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * Update Console Status via AJAX
 */
function updateConsoleStatus(consoleId, status) {
    fetch(`/admin/console/${consoleId}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Status console berhasil diperbarui', 'success');
        } else {
            showToast('Gagal memperbarui status console', 'danger');
        }
    })
    .catch(() => {
        showToast('Terjadi kesalahan', 'danger');
    });
}

// ===== Notification System (polling + dropdown) =====
(function () {
    function escapeHtml(text) {
        if (text == null || text === '') {
            return '';
        }
        const d = document.createElement('div');
        d.textContent = text;
        return d.innerHTML;
    }

    function renderNotificationsFromPayload(data) {
        const listEl = document.getElementById('notificationList');
        const headerEl = document.querySelector('#notificationDropdown .notification-header');
        const toggleBtn = document.getElementById('notificationToggle');
        if (!listEl || !toggleBtn) {
            return;
        }

        const notifications = data.notifications || [];
        const unreadCount = typeof data.unread_count === 'number' ? data.unread_count : 0;

        let badge = toggleBtn.querySelector('.notification-badge');
        if (unreadCount > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'notification-badge';
                toggleBtn.appendChild(badge);
            }
            badge.textContent = String(unreadCount);
        } else if (badge) {
            badge.remove();
        }

        if (headerEl) {
            const markAll = unreadCount > 0
                ? '<button type="button" class="notification-mark-all" id="markAllRead"><small>Tandai semua dibaca</small></button>'
                : '';
            headerEl.innerHTML = '<h6 class="mb-0">Notifikasi</h6><div class="d-flex align-items-center gap-2">' + markAll + '<button type="button" class="notification-close" id="notificationClose" aria-label="Tutup"><i class="fas fa-times"></i></button></div>';
        }

        if (!notifications.length) {
            listEl.innerHTML = '<div class="notification-empty"><i class="fas fa-bell-slash"></i><p>Belum ada notifikasi</p></div>';
            return;
        }

        listEl.innerHTML = notifications.map(function (n) {
            const unread = !n.is_read;
            const linkEsc = n.link ? escapeHtml(n.link) : '';
            const dot = unread ? '<span class="notification-dot"></span>' : '';
            return (
                '<div class="notification-item ' + (unread ? 'unread' : 'read') + '" data-id="' + String(n.id) + '" data-link="' + linkEsc + '">' +
                '<div class="notification-content">' +
                '<div class="notification-title">' + escapeHtml(n.title) + '</div>' +
                '<div class="notification-message">' + escapeHtml(n.message) + '</div>' +
                '<div class="notification-time">' + escapeHtml(n.created_at_human || '') + '</div>' +
                '</div>' + dot + '</div>'
            );
        }).join('');
    }

    function fetchNotificationsJson() {
        return fetch('/notifications', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        }).then(function (r) {
            return r.json();
        });
    }

    function pollNotifications() {
        return fetchNotificationsJson()
            .then(renderNotificationsFromPayload)
            .catch(function () {});
    }

    window.psRentalRefreshNotifications = pollNotifications;

    document.addEventListener('DOMContentLoaded', function () {
        const notificationToggle = document.getElementById('notificationToggle');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const listEl = document.getElementById('notificationList');

        if (!notificationToggle || !notificationDropdown || !listEl) {
            return;
        }

        notificationToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('active');
            pollNotifications();
        });

        document.addEventListener('click', function (e) {
            if (!notificationDropdown.contains(e.target) && !notificationToggle.contains(e.target)) {
                notificationDropdown.classList.remove('active');
            }
        });

        listEl.addEventListener('click', function (e) {
            const item = e.target.closest('.notification-item');
            if (!item) {
                return;
            }

            const id = item.dataset.id;
            const link = item.dataset.link || '';

            function navigate() {
                if (link) {
                    window.location.href = link;
                }
            }

            if (!item.classList.contains('read')) {
                fetch('/notifications/' + id + '/read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    },
                })
                    .then(function () {
                        item.classList.remove('unread');
                        item.classList.add('read');
                        const dot = item.querySelector('.notification-dot');
                        if (dot) {
                            dot.remove();
                        }
                        const badge = notificationToggle.querySelector('.notification-badge');
                        if (badge) {
                            const count = parseInt(badge.textContent, 10) - 1;
                            if (count > 0) {
                                badge.textContent = String(count);
                            } else {
                                badge.remove();
                            }
                        }
                        return pollNotifications();
                    })
                    .finally(navigate);
            } else {
                navigate();
            }
        });

        notificationDropdown.addEventListener('click', function (e) {
            const closeBtn = e.target.closest('#notificationClose');
            if (closeBtn) {
                e.stopPropagation();
                notificationDropdown.classList.remove('active');
                return;
            }

            const markBtn = e.target.closest('#markAllRead');
            if (!markBtn) {
                return;
            }
            e.stopPropagation();
            fetch('/notifications/read-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                },
            })
                .then(function () {
                    return pollNotifications();
                })
                .catch(function () {});
        });

        setInterval(pollNotifications, 12000);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                pollNotifications();
            }
        });
        setTimeout(pollNotifications, 1500);
    });
})();