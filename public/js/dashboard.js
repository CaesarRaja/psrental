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
        var wrappers = document.querySelectorAll('.notification-wrapper');
        if (!wrappers.length) {
            return;
        }

        var notifications = data.notifications || [];
        var unreadCount = typeof data.unread_count === 'number' ? data.unread_count : 0;

        wrappers.forEach(function (wrapper) {
            var toggleBtn = wrapper.querySelector('.notification-toggle');
            var listEl = wrapper.querySelector('.notification-list');
            var headerEl = wrapper.querySelector('.notification-header');
            if (!listEl || !toggleBtn) {
                return;
            }

            var badge = toggleBtn.querySelector('.notification-badge');
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
                var markAll = unreadCount > 0
                    ? '<button type="button" class="notification-mark-all mark-all-read"><small>Tandai semua dibaca</small></button>'
                    : '';
                headerEl.innerHTML = '<h6 class="mb-0">Notifikasi</h6><div class="d-flex align-items-center gap-2">' + markAll + '<button type="button" class="notification-close" aria-label="Tutup"><i class="fas fa-times"></i></button></div>';
            }

            if (!notifications.length) {
                listEl.innerHTML = '<div class="notification-empty"><i class="fas fa-bell-slash"></i><p>Belum ada notifikasi</p></div>';
                return;
            }

            listEl.innerHTML = notifications.map(function (n) {
                var unread = !n.is_read;
                var linkEsc = n.link ? escapeHtml(n.link) : '';
                var dot = unread ? '<span class="notification-dot"></span>' : '';
                return (
                    '<div class="notification-item ' + (unread ? 'unread' : 'read') + '" data-id="' + String(n.id) + '" data-link="' + linkEsc + '">' +
                    '<div class="notification-content">' +
                    '<div class="notification-title">' + escapeHtml(n.title) + '</div>' +
                    '<div class="notification-message">' + escapeHtml(n.message) + '</div>' +
                    '<div class="notification-time">' + escapeHtml(n.created_at_human || '') + '</div>' +
                    '</div>' + dot + '</div>'
                );
            }).join('');
        });
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
        var wrappers = document.querySelectorAll('.notification-wrapper');
        if (!wrappers.length) {
            return;
        }

        wrappers.forEach(function (wrapper) {
            var notificationToggle = wrapper.querySelector('.notification-toggle');
            var notificationDropdown = wrapper.querySelector('.notification-dropdown');
            var listEl = wrapper.querySelector('.notification-list');

            if (!notificationToggle || !notificationDropdown || !listEl) {
                return;
            }

            notificationToggle.addEventListener('click', function (e) {
                e.stopPropagation();
                notificationDropdown.classList.toggle('active');
                pollNotifications();
            });

            listEl.addEventListener('click', function (e) {
                var item = e.target.closest('.notification-item');
                if (!item) {
                    return;
                }

                var id = item.dataset.id;
                var link = item.dataset.link || '';

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
                            var dot = item.querySelector('.notification-dot');
                            if (dot) {
                                dot.remove();
                            }
                            var badge = notificationToggle.querySelector('.notification-badge');
                            if (badge) {
                                var count = parseInt(badge.textContent, 10) - 1;
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
                var closeBtn = e.target.closest('.notification-close');
                if (closeBtn) {
                    e.stopPropagation();
                    notificationDropdown.classList.remove('active');
                    return;
                }

                var markBtn = e.target.closest('.mark-all-read');
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
        });

        document.addEventListener('click', function (e) {
            wrappers.forEach(function (wrapper) {
                var toggle = wrapper.querySelector('.notification-toggle');
                var dropdown = wrapper.querySelector('.notification-dropdown');
                if (dropdown && dropdown.classList.contains('active') && !dropdown.contains(e.target) && !toggle.contains(e.target)) {
                    dropdown.classList.remove('active');
                }
            });
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