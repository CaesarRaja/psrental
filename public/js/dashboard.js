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

    // Notification Click
    const notificationBtn = document.querySelector('.notification-btn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function() {
            showToast('Kamu memiliki 3 notifikasi baru', 'info');
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