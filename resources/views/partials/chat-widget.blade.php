@php
    $isAdmin = auth()->user()->isAdmin();
    $currentUserId = auth()->id();
@endphp

<!-- Chat Widget Wrapper -->
<div id="chatWidget" class="chat-widget-wrapper" data-is-admin="{{ $isAdmin ? 'true' : 'false' }}">
    <!-- Chat Launcher Button (Floating Action Button) -->
    <button id="chatLauncher" class="chat-launcher">
        <i class="fas fa-comment-dots"></i>
        <span class="chat-launcher-badge d-none" id="globalUnreadBadge">0</span>
    </button>

    <!-- Chat Main Window -->
    <div id="chatWindow" class="chat-window d-none">
        <!-- Main Full-Width Header (Shopee Style) -->
        <div class="chat-main-header">
            <div class="chat-header-left">
                <span class="chat-title-text">Chat</span>
            </div>
            <div class="chat-header-right">
                <button class="chat-header-action-btn" id="btnMaximize" title="Maximize/Restore">
                    <i class="fas fa-expand-arrows-alt"></i>
                </button>
                <button class="chat-header-action-btn" id="btnMinimize" title="Minimize">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        </div>

        @if(!$isAdmin)
            <!-- CUSTOMER LAYOUT (Single Chat Area) -->
            <div class="chat-container customer-chat">
                <!-- Active User Subheader -->
                <div class="chat-subheader">
                    <span class="chat-active-user-name">Customer Service <i class="fas fa-chevron-down ms-1 text-muted" style="font-size: 10px;"></i></span>
                    <span class="chat-active-user-status" id="customerAdminStatusWrap"><span class="status-dot" id="customerAdminStatusDot"></span> <span id="customerAdminStatusText">Memuat...</span></span>
                </div>

                <!-- Messages Body -->
                <div class="chat-body" id="customerChatBody">
                    <div class="chat-welcome-msg">
                        <i class="fas fa-headset mb-2" style="font-size: 24px; color: var(--primary);"></i>
                        <p class="mb-1 fw-medium" style="color: var(--text-primary);">Halo, {{ auth()->user()->name }}!</p>
                        <p class="text-muted small">Ada yang bisa kami bantu? Silakan tulis pertanyaan atau keluhan Anda di bawah ini.</p>
                    </div>
                    <div class="chat-messages-list" id="customerMessagesList">
                        <!-- Messages loaded dynamically -->
                    </div>
                </div>

                <!-- Footer Input Area -->
                <div class="chat-footer">
                    <form id="customerChatForm" autocomplete="off">
                        @csrf
                        <div class="chat-input-row">
                            <textarea id="customerMessageInput" class="chat-textarea" placeholder="Tulis pesan..." required rows="1"></textarea>
                            <button type="submit" class="chat-send-btn" id="customerSendBtn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <!-- ADMIN LAYOUT (2 Columns - Shopee style) -->
            <div class="chat-container admin-chat">
                <!-- Left Panel: Sidebar Conversations -->
                <div class="chat-sidebar">
                    <div class="chat-sidebar-search-row">
                        <div class="chat-search-wrap">
                            <i class="fas fa-search chat-search-icon"></i>
                            <input type="text" id="adminSearchInput" class="chat-search-input" placeholder="Cari nama">
                        </div>
                        <div class="chat-filter-wrap">
                            <span>Semua <i class="fas fa-chevron-down ms-1" style="font-size: 8px;"></i></span>
                        </div>
                    </div>
                    <div class="chat-conversations-list" id="adminConversationsList">
                        <!-- Conversations loaded dynamically -->
                    </div>
                </div>

                <!-- Right Panel: Active Chat Area -->
                <div class="chat-main-area">
                    <!-- Active User Subheader -->
                    <div class="chat-subheader" id="adminActiveUserSubheader">
                        <span class="chat-active-user-name" id="activeCustomerName">Pilih Customer <i class="fas fa-chevron-down ms-1 text-muted" style="font-size: 10px;"></i></span>
                        <span class="chat-active-user-status d-none" id="activeCustomerStatusWrap"><span class="status-dot" id="activeCustomerStatusDot"></span> <span id="activeCustomerStatusText">Memuat...</span></span>
                    </div>

                    <!-- Messages Body -->
                    <div class="chat-body" id="adminChatBody">
                        <!-- Default Blank Slate -->
                        <div class="chat-welcome-msg" id="adminBlankSlate" style="margin-top: 40px;">
                            <i class="fas fa-comments mb-3" style="font-size: 48px; color: var(--primary); opacity: 0.8;"></i>
                            <h5 class="fw-semibold" style="color: var(--text-primary);">Selamat Datang di Portal Chat</h5>
                            <p class="text-muted small">Pilih salah satu customer di panel kiri untuk mulai membalas pesan secara real-time.</p>
                        </div>
                        
                        <!-- Real Messages Container (Initially hidden) -->
                        <div class="chat-messages-list d-none" id="adminMessagesList">
                            <!-- Messages loaded dynamically -->
                        </div>
                    </div>

                    <!-- Footer Input Area (Initially hidden) -->
                    <div class="chat-footer d-none" id="adminChatFooter">
                        <form id="adminChatForm" autocomplete="off">
                            @csrf
                            <input type="hidden" id="activeCustomerId" value="">
                            <div class="chat-input-row">
                                <textarea id="adminMessageInput" class="chat-textarea" placeholder="Tulis pesan..." required rows="1"></textarea>
                                <button type="submit" class="chat-send-btn" id="adminSendBtn">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatWidget = document.getElementById('chatWidget');
    const chatLauncher = document.getElementById('chatLauncher');
    const chatWindow = document.getElementById('chatWindow');
    const globalUnreadBadge = document.getElementById('globalUnreadBadge');
    
    const btnMinimize = document.querySelectorAll('#btnMinimize');
    const btnMaximize = document.querySelectorAll('#btnMaximize');

    const isAdmin = chatWidget.dataset.isAdmin === 'true';
    let pollingInterval = null;
    let unreadCountInterval = null;
    let currentActiveCustomerId = null; // For Admin only

    // Declare functions at DOMContentLoaded scope to avoid block-scoping ReferenceErrors
    let loadConversations = function(search = '') {};
    let loadMessages = function(customerIdOrSilent = false, silent = false) {};

    // ----------------------------------------
    // Toggle Window (Open / Minimize)
    // ----------------------------------------
    chatLauncher.addEventListener('click', function() {
        chatWindow.classList.toggle('d-none');
        chatLauncher.classList.toggle('d-none');
        
        if (!chatWindow.classList.contains('d-none')) {
            // Mark open state
            if (isAdmin) {
                loadConversations();
                if (currentActiveCustomerId) {
                    loadMessages(currentActiveCustomerId);
                }
            } else {
                loadMessages();
            }
            startPolling();
            setTimeout(scrollToBottom, 200);
        } else {
            stopPolling();
        }
    });

    btnMinimize.forEach(btn => {
        btn.addEventListener('click', function() {
            chatWindow.classList.add('d-none');
            chatLauncher.classList.remove('d-none');
            stopPolling();
            updateUnreadCount(); // Check count on collapse
        });
    });

    // ----------------------------------------
    // Maximize / Restore Size
    // ----------------------------------------
    btnMaximize.forEach(btn => {
        btn.addEventListener('click', function() {
            chatWidget.classList.toggle('chat-maximized');
            const isMaximized = chatWidget.classList.contains('chat-maximized');
            
            // Change icons on all maximize buttons
            btnMaximize.forEach(b => {
                b.innerHTML = isMaximized ? '<i class="fas fa-compress-arrows-alt"></i>' : '<i class="fas fa-expand-arrows-alt"></i>';
                b.title = isMaximized ? 'Restore' : 'Maximize';
            });
            
            // Auto scroll messages to bottom after size transition
            setTimeout(scrollToBottom, 300);
        });
    });

    // ----------------------------------------
    // Auto-expanding textarea & Active send button styling
    // ----------------------------------------
    const textareas = document.querySelectorAll('.chat-textarea');
    textareas.forEach(ta => {
        ta.addEventListener('input', function() {
            // Auto expand height
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
            if (this.scrollHeight > 120) {
                this.style.overflowY = 'auto';
            } else {
                this.style.overflowY = 'hidden';
            }
            
            // Send button active styling
            const form = this.closest('form');
            const sendBtn = form.querySelector('.chat-send-btn');
            if (this.value.trim().length > 0) {
                sendBtn.classList.add('active');
            } else {
                sendBtn.classList.remove('active');
            }
        });

        // Submit on enter key (without shift)
        ta.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                const form = this.closest('form');
                form.dispatchEvent(new Event('submit', { cancelable: true }));
            }
        });
    });

    // ----------------------------------------
    // Auto-scroll to bottom of chat
    // ----------------------------------------
    function scrollToBottom() {
        const bodyId = isAdmin ? 'adminChatBody' : 'customerChatBody';
        const body = document.getElementById(bodyId);
        if (body) {
            body.scrollTop = body.scrollHeight;
        }
    }

    // ----------------------------------------
    // Dynamic Polling Management
    // ----------------------------------------
    function startPolling() {
        stopPolling(); // Ensure clear previous
        
        // Immediate call
        if (isAdmin) {
            loadConversations();
            if (currentActiveCustomerId) {
                loadMessages(currentActiveCustomerId);
            }
        } else {
            loadMessages();
        }

        // Interval every 2.5 seconds
        pollingInterval = setInterval(function() {
            if (isAdmin) {
                loadConversations();
                if (currentActiveCustomerId) {
                    loadMessages(currentActiveCustomerId, true); // true to skip loader
                }
            } else {
                loadMessages(true); // true to skip loader
            }
        }, 2500);
    }

    function stopPolling() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
    }

    // Unread count polling while chat window is closed
    unreadCountInterval = setInterval(updateUnreadCount, 5000);
    updateUnreadCount(); // Initial check

    function updateUnreadCount() {
        if (!chatWindow.classList.contains('d-none')) {
            return; // Chat is open, badge not needed
        }
        
        fetch('{{ route("chat.unreadCount") }}')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.unread_count > 0) {
                    globalUnreadBadge.textContent = data.unread_count;
                    globalUnreadBadge.classList.remove('d-none');
                } else {
                    globalUnreadBadge.classList.add('d-none');
                }
            })
            .catch(err => console.error("Error updating unread count:", err));
    }

    // ----------------------------------------
    // CUSTOMER LOGIC
    // ----------------------------------------
    if (!isAdmin) {
        const customerForm = document.getElementById('customerChatForm');
        const customerInput = document.getElementById('customerMessageInput');
        const customerSendBtn = document.getElementById('customerSendBtn');
        const customerMessagesList = document.getElementById('customerMessagesList');

        loadMessages = function(silent = false) {
            fetch('{{ route("chat.messages") }}')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        renderMessages(data.messages, customerMessagesList, silent);
                        // Update admin online/offline status
                        updateOnlineStatus('customer', data.other_user_online);
                    }
                })
                .catch(err => console.error("Error loading customer messages:", err));
        };

        customerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const messageText = customerInput.value.trim();
            if (!messageText) return;

            customerInput.value = ''; // Clear input immediately for feel
            customerInput.style.height = 'auto';
            customerSendBtn.classList.remove('active');

            fetch('{{ route("chat.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ message: messageText })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    appendSingleMessage(data.chat, customerMessagesList);
                    scrollToBottom();
                }
            })
            .catch(err => console.error("Error sending message:", err));
        });
    }

    // ----------------------------------------
    // ADMIN LOGIC
    // ----------------------------------------
    if (isAdmin) {
        const adminSearchInput = document.getElementById('adminSearchInput');
        const adminConversationsList = document.getElementById('adminConversationsList');
        const adminMessagesList = document.getElementById('adminMessagesList');
        const adminChatForm = document.getElementById('adminChatForm');
        const adminMessageInput = document.getElementById('adminMessageInput');
        const adminSendBtn = document.getElementById('adminSendBtn');
        const activeCustomerIdInput = document.getElementById('activeCustomerId');
        
        const adminBlankSlate = document.getElementById('adminBlankSlate');
        const adminChatFooter = document.getElementById('adminChatFooter');
        
        const activeCustomerName = document.getElementById('activeCustomerName');
        const activeCustomerStatusWrap = document.getElementById('activeCustomerStatusWrap');
        const activeCustomerStatusText = document.getElementById('activeCustomerStatusText');

        // Search trigger
        adminSearchInput.addEventListener('input', function() {
            loadConversations(adminSearchInput.value);
        });

        loadConversations = function(search = '') {
            fetch(`{{ route("chat.conversations") }}?search=${encodeURIComponent(search)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        renderConversations(data.conversations);
                    }
                })
                .catch(err => console.error("Error loading conversations:", err));
        };

        function renderConversations(conversations) {
            adminConversationsList.innerHTML = '';
            
            if (conversations.length === 0) {
                adminConversationsList.innerHTML = `
                    <div class="text-center py-4 text-muted small">
                        <i class="fas fa-user-slash mb-2 d-block"></i>
                        Customer tidak ditemukan
                    </div>
                `;
                return;
            }

            conversations.forEach(c => {
                const isActive = currentActiveCustomerId == c.id ? 'active' : '';
                const unreadBadge = c.unread_count > 0 
                    ? `<span class="convo-badge bg-danger text-white">${c.unread_count}</span>` 
                    : '';
                const latestMsgSnippet = c.latest_message.length > 25 
                    ? c.latest_message.substring(0, 25) + '...' 
                    : c.latest_message;

                const item = document.createElement('div');
                item.className = `conversation-item ${isActive}`;
                item.dataset.customerId = c.id;
                item.dataset.customerName = c.name;
                item.dataset.customerInitials = c.initials;

                const onlineDot = c.is_online 
                    ? '<span class="convo-online-dot"></span>' 
                    : '';

                item.innerHTML = `
                    <div class="convo-avatar">
                        ${c.initials}
                        ${onlineDot}
                    </div>
                    <div class="convo-details">
                        <div class="convo-header">
                            <span class="convo-name">${c.name}</span>
                            <span class="convo-time">${c.latest_message_time}</span>
                        </div>
                        <div class="convo-body">
                            <span class="convo-message">${latestMsgSnippet}</span>
                            ${unreadBadge}
                        </div>
                    </div>
                `;

                // Handle click to switch active customer
                item.addEventListener('click', function() {
                    const cid = this.dataset.customerId;
                    const cname = this.dataset.customerName;
                    
                    // Switch active classes in UI list
                    document.querySelectorAll('.conversation-item').forEach(el => el.classList.remove('active'));
                    this.classList.add('active');

                    // Setup right panel headers & variables
                    currentActiveCustomerId = cid;
                    activeCustomerIdInput.value = cid;
                    activeCustomerName.innerHTML = `${cname} <i class="fas fa-chevron-down ms-1 text-muted" style="font-size: 10px;"></i>`;
                    
                    activeCustomerStatusWrap.classList.remove('d-none');

                    // Reveal components
                    adminBlankSlate.classList.add('d-none');
                    adminMessagesList.classList.remove('d-none');
                    adminChatFooter.classList.remove('d-none');

                    // Fetch messages
                    loadMessages(cid);
                    setTimeout(scrollToBottom, 200);
                });

                adminConversationsList.appendChild(item);
            });
        }

        loadMessages = function(customerId, silent = false) {
            if (!customerId) return;
            
            fetch(`{{ route("chat.messages") }}?customer_id=${customerId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && currentActiveCustomerId == customerId) {
                        renderMessages(data.messages, adminMessagesList, silent);
                        // Update customer online/offline status
                        updateOnlineStatus('admin', data.other_user_online);
                    }
                })
                .catch(err => console.error("Error loading admin messages:", err));
        };

        adminChatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const messageText = adminMessageInput.value.trim();
            const cid = activeCustomerIdInput.value;
            
            if (!messageText || !cid) return;

            adminMessageInput.value = '';
            adminMessageInput.style.height = 'auto';
            adminSendBtn.classList.remove('active');

            fetch('{{ route("chat.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    message: messageText,
                    receiver_id: cid
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && currentActiveCustomerId == cid) {
                    appendSingleMessage(data.chat, adminMessagesList);
                    scrollToBottom();
                    loadConversations(); // Reload sidebar list for latest snippet
                }
            })
            .catch(err => console.error("Error sending admin message:", err));
        });
    }

    // ----------------------------------------
    // SHARED RENDER HELPERS
    // ----------------------------------------

    // Update online/offline status display
    function updateOnlineStatus(context, isOnline) {
        let statusDot, statusText, statusWrap;

        if (context === 'customer') {
            statusDot = document.getElementById('customerAdminStatusDot');
            statusText = document.getElementById('customerAdminStatusText');
            statusWrap = document.getElementById('customerAdminStatusWrap');
        } else {
            statusDot = document.getElementById('activeCustomerStatusDot');
            statusText = document.getElementById('activeCustomerStatusText');
            statusWrap = document.getElementById('activeCustomerStatusWrap');
        }

        if (statusDot && statusText) {
            if (isOnline) {
                statusDot.classList.remove('offline');
                statusText.textContent = 'Online';
                if (statusWrap) {
                    statusWrap.classList.remove('status-offline');
                    statusWrap.classList.add('status-online');
                }
            } else {
                statusDot.classList.add('offline');
                statusText.textContent = 'Offline';
                if (statusWrap) {
                    statusWrap.classList.remove('status-online');
                    statusWrap.classList.add('status-offline');
                }
            }
        }
    }

    function renderMessages(messages, container, silent) {
        // Save current scroll state
        const isNearBottom = container.parentElement.scrollHeight - container.parentElement.scrollTop <= container.parentElement.clientHeight + 100;
        
        let html = '';
        if (messages.length === 0) {
            html = `
                <div class="text-center py-5 text-muted small">
                    <i class="far fa-comments mb-2 d-block" style="font-size: 20px;"></i>
                    Belum ada riwayat percakapan.<br>Mulai obrolan Anda sekarang!
                </div>
            `;
            container.innerHTML = html;
            return;
        }

        let lastDate = '';
        messages.forEach(msg => {
            // Render date divider
            if (msg.date !== lastDate) {
                html += `<div class="chat-date-divider"><span>${msg.date}</span></div>`;
                lastDate = msg.date;
            }

            const senderClass = msg.is_sender ? 'outgoing' : 'incoming';
            const readIcon = msg.is_sender 
                ? (msg.is_read ? '<i class="fas fa-check-double ms-1"></i>' : '<i class="fas fa-check ms-1"></i>')
                : '';

            html += `
                <div class="chat-message-bubble ${senderClass}">
                    <div class="bubble-content">
                        <span class="bubble-text">${escapeHtml(msg.message)}</span>
                        <span class="bubble-time">${msg.time} ${readIcon}</span>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;

        // Auto scroll if it is first load OR user was already at the bottom
        if (!silent || isNearBottom) {
            scrollToBottom();
        }
    }

    function appendSingleMessage(msg, container) {
        // Remove empty state if visible
        const emptyState = container.querySelector('.text-center');
        if (emptyState) {
            emptyState.remove();
        }

        const readIcon = msg.is_sender 
            ? (msg.is_read ? '<i class="fas fa-check-double ms-1"></i>' : '<i class="fas fa-check ms-1"></i>')
            : '';

        const bubble = document.createElement('div');
        bubble.className = `chat-message-bubble outgoing`;
        bubble.innerHTML = `
            <div class="bubble-content">
                <span class="bubble-text">${escapeHtml(msg.message)}</span>
                <span class="bubble-time">${msg.time} ${readIcon}</span>
            </div>
        `;
        container.appendChild(bubble);
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
});
</script>
@endpush
