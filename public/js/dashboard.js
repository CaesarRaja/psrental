/**
 * PS Rent Station - Dashboard JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Sidebar Toggle for Mobile
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 991 && sidebar) {
            const toggle = document.querySelector('.sidebar-toggle');
            const sidebarContains = sidebar.contains(e.target);
            const toggleContains = toggle && toggle.contains ? toggle.contains(e.target) : false;
            
            if (!sidebarContains && !toggleContains) {
                sidebar.classList.remove('active');
            }
        }
    });

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

// ===== Notification System =====
document.addEventListener('DOMContentLoaded', function() {
    const notificationToggle = document.getElementById('notificationToggle');
    const notificationDropdown = document.getElementById('notificationDropdown');

    if (notificationToggle && notificationDropdown) {
        // Toggle dropdown
        notificationToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!notificationDropdown.contains(e.target) && !notificationToggle.contains(e.target)) {
                notificationDropdown.classList.remove('active');
            }
        });

        // Mark individual notification as read
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function(e) {
                const id = this.dataset.id;
                const link = this.dataset.link;

                const navigate = () => {
                    if (link) {
                        window.location.href = link;
                    }
                };

                if (!this.classList.contains('read')) {
                    fetch(`/notifications/${id}/read`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Accept': 'application/json',
                        }
                    })
                    .then(() => {
                        this.classList.remove('unread');
                        this.classList.add('read');
                        const dot = this.querySelector('.notification-dot');
                        if (dot) dot.remove();

                        // Update badge count
                        const badge = notificationToggle.querySelector('.notification-badge');
                        if (badge) {
                            const count = parseInt(badge.textContent) - 1;
                            if (count > 0) {
                                badge.textContent = count;
                            } else {
                                badge.remove();
                            }
                        }
                    })
                    .finally(navigate);
                } else {
                    navigate();
                }
            });
        });

        // Mark all as read
        const markAllBtn = document.getElementById('markAllRead');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                fetch('/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    }
                })
                .then(() => {
                    document.querySelectorAll('.notification-item.unread').forEach(item => {
                        item.classList.remove('unread');
                        item.classList.add('read');
                        const dot = item.querySelector('.notification-dot');
                        if (dot) dot.remove();
                    });
                    const badge = notificationToggle.querySelector('.notification-badge');
                    if (badge) badge.remove();
                    markAllBtn.remove();
                })
                .catch(() => {});
            });
        }
    }
});