@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid py-4" style="height: calc(100vh - 120px);">
    <div class="row g-4 h-100">
        
        {{-- KIRI: LIST THREADS --}}
        <div class="col-lg-4 h-100 d-flex flex-column">
            <div class="card border-0 shadow-sm rounded-4 flex-grow-1 overflow-hidden">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <h5 class="fw-bold mb-1"><i class="fas fa-comments text-primary me-2"></i>Live Chat CS</h5>
                    <p class="text-muted small mb-0">Kelola dan balas percakapan dari B2C & B2B Reseller</p>
                </div>
                
                {{-- Search Bar Thread --}}
                <div class="px-4 pb-3">
                    <div class="input-group bg-light rounded-pill px-3 py-1">
                        <span class="input-group-text bg-transparent border-0 text-muted p-0 me-2"><i class="fas fa-search"></i></span>
                        <input type="text" id="thread-search" class="form-control bg-transparent border-0 p-0" placeholder="Cari pelanggan..." style="font-size: 0.88rem;">
                    </div>
                </div>

                {{-- List Container --}}
                <div class="card-body p-0 overflow-y-auto flex-grow-1" id="threads-list" style="max-height: calc(100vh - 270px);">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KANAN: CHAT AREA --}}
        <div class="col-lg-8 h-100 d-flex flex-column">
            
            {{-- Active Chat View --}}
            <div id="active-chat-card" class="card border-0 shadow-sm rounded-4 flex-grow-1 overflow-hidden h-100 d-flex flex-column" style="display: none !important;">
                
                {{-- Chat Header --}}
                <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <img src="data:image/svg+xml;utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100' width='100%25' height='100%25'%3E%3Ccircle cx='50' cy='50' r='50' fill='%230d6efd'/%3E%3Ctext x='50%25' y='55%25' dominant-baseline='middle' text-anchor='middle' font-size='50' font-family='Poppins' font-weight='bold' fill='%23ffffff'%3EU%3C/text%3E%3C/svg%3E" id="chat-client-avatar" alt="Avatar" class="rounded-circle" style="width: 45px; height: 45px;">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="fw-bold mb-0" id="chat-client-name">Pelanggan</h6>
                                <span class="badge" id="chat-client-badge">B2C</span>
                            </div>
                            <small class="text-muted" id="chat-client-email">email@domain.com</small>
                        </div>
                    </div>
                    
                    {{-- User Details Toggle --}}
                    <button class="btn btn-sm btn-outline-light text-dark rounded-circle" id="toggle-info-btn" title="Detail Profil">
                        <i class="fas fa-info-circle fs-5"></i>
                    </button>
                </div>

                <div class="d-flex flex-row flex-grow-1 h-100 overflow-hidden">
                    
                    {{-- Conversation History Pane --}}
                    <div class="d-flex flex-column flex-grow-1 h-100 overflow-hidden bg-light">
                        
                        {{-- Messages Area --}}
                        <div class="p-4 overflow-y-auto flex-grow-1" id="chat-messages-container" style="max-height: calc(100vh - 350px);">
                            {{-- Messages get dynamically rendered here --}}
                        </div>

                        {{-- Footer Reply Input --}}
                        <div class="p-3 bg-white border-top">
                            <form id="admin-chat-form" class="d-flex gap-3">
                                <input type="text" id="admin-chat-input" class="form-control rounded-pill border-0 px-4 py-2-5" placeholder="Tulis balasan Anda..." style="background: #f1f3f5; font-size: 0.9rem;" autocomplete="off" required>
                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2">
                                    <span>Kirim</span>
                                    <i class="fas fa-paper-plane text-white" style="font-size: 0.85rem;"></i>
                                </button>
                            </form>
                        </div>

                    </div>

                    {{-- Client Profile Info Sidebar --}}
                    <div class="border-start bg-white h-100 p-4" id="client-info-sidebar" style="width: 280px; display: none;">
                        <h6 class="fw-bold border-bottom pb-2 mb-3"><i class="fas fa-user-circle me-1 text-primary"></i>Profil Pelanggan</h6>
                        
                        <div class="mb-3">
                            <label class="text-muted small fw-semibold d-block mb-1">Nama Lengkap</label>
                            <span class="fw-bold d-block text-dark" id="side-client-name">-</span>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small fw-semibold d-block mb-1">Email</label>
                            <span class="fw-semibold d-block text-dark" id="side-client-email">-</span>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small fw-semibold d-block mb-1">No. Telepon</label>
                            <span class="fw-semibold d-block text-dark" id="side-client-phone">-</span>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small fw-semibold d-block mb-1">Alamat Pengiriman</label>
                            <span class="small d-block text-dark" id="side-client-address">-</span>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small fw-semibold d-block mb-1">Tipe Akun</label>
                            <span id="side-client-tag" class="badge">Customer</span>
                        </div>
                    </div>

                </div>

            </div>

            {{-- Placeholder View (When no thread is active) --}}
            <div id="chat-placeholder-card" class="card border-0 shadow-sm rounded-4 flex-grow-1 d-flex flex-column justify-content-center align-items-center text-center p-5">
                <div class="bg-primary bg-opacity-10 rounded-circle p-4 mb-3">
                    <i class="fas fa-comments fa-3x text-primary"></i>
                </div>
                <h5 class="fw-bold">Selamat Datang di Portal Chat CS</h5>
                <p class="text-muted max-w-sm" style="max-width: 400px;">Pilih salah satu percakapan di bilah kiri untuk melihat riwayat pesan dan mulai membalas chat pelanggan secara real-time.</p>
            </div>

        </div>

    </div>
</div>

<style>
    /* Styling scrollbar */
    #threads-list::-webkit-scrollbar,
    #chat-messages-container::-webkit-scrollbar {
        width: 6px;
    }
    #threads-list::-webkit-scrollbar-track,
    #chat-messages-container::-webkit-scrollbar-track {
        background: transparent;
    }
    #threads-list::-webkit-scrollbar-thumb,
    #chat-messages-container::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.08);
        border-radius: 10px;
    }

    /* Thread Hover */
    .thread-item {
        cursor: pointer;
        transition: all 0.2s ease;
        border-bottom: 1px solid #f8f9fa;
    }
    .thread-item:hover {
        background-color: #f8f9fa;
    }
    .thread-item.active {
        background-color: #e9f2ff;
        border-left: 4px solid #0d6efd;
    }

    /* Message Bubbles */
    .chat-bubble {
        max-width: 70%;
        padding: 12px 16px;
        border-radius: 16px;
        font-size: 0.9rem;
        line-height: 1.45;
        margin-bottom: 10px;
        word-wrap: break-word;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .chat-bubble-received {
        align-self: flex-start;
        background-color: white;
        color: #212529;
        border-bottom-left-radius: 4px;
        border: 1px solid #e9ecef;
    }
    .chat-bubble-sent {
        align-self: flex-end;
        background-color: #0d6efd;
        color: white;
        border-bottom-right-radius: 4px;
    }
    .chat-time {
        font-size: 0.65rem;
        display: block;
        margin-top: 4px;
        text-align: right;
        opacity: 0.8;
    }
</style>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Local high-performance SVG avatar generator
        function getAvatarSvg(name) {
            const letter = name ? name.charAt(0).toUpperCase() : '?';
            const colors = ['%230d6efd', '%2320c997', '%23fd7e14', '%236f42c1', '%23d63384', '%230dcaf0', '%23198754'];
            const charCode = name ? name.charCodeAt(0) : 0;
            const color = colors[charCode % colors.length];
            
            return `data:image/svg+xml;utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100' width='100%25' height='100%25'%3E%3Ccircle cx='50' cy='50' r='50' fill='${color}'/%3E%3Ctext x='50%25' y='55%25' dominant-baseline='middle' text-anchor='middle' font-size='50' font-family='Poppins, sans-serif' font-weight='bold' fill='%23ffffff'%3E${letter}%3C/text%3E%3C/svg%3E`;
        }

        const threadsList = document.getElementById('threads-list');
        const activeChatCard = document.getElementById('active-chat-card');
        const placeholderCard = document.getElementById('chat-placeholder-card');
        const searchInput = document.getElementById('thread-search');
        
        const chatClientName = document.getElementById('chat-client-name');
        const chatClientEmail = document.getElementById('chat-client-email');
        const chatClientBadge = document.getElementById('chat-client-badge');
        const chatClientAvatar = document.getElementById('chat-client-avatar');
        
        const sideClientName = document.getElementById('side-client-name');
        const sideClientEmail = document.getElementById('side-client-email');
        const sideClientPhone = document.getElementById('side-client-phone');
        const sideClientAddress = document.getElementById('side-client-address');
        const sideClientTag = document.getElementById('side-client-tag');
        
        const messagesContainer = document.getElementById('chat-messages-container');
        const adminChatForm = document.getElementById('admin-chat-form');
        const adminChatInput = document.getElementById('admin-chat-input');
        
        const toggleInfoBtn = document.getElementById('toggle-info-btn');
        const clientSidebar = document.getElementById('client-info-sidebar');

        let activeUserId = null;
        let allThreads = [];
        let pollingInterval = null;
        let showSidebar = false;

        // Toggle Sidebar Profil
        toggleInfoBtn.addEventListener('click', function() {
            showSidebar = !showSidebar;
            clientSidebar.style.display = showSidebar ? 'block' : 'none';
        });

        // Load Threads initially and poll
        loadThreads();
        setInterval(loadThreads, 4000); // Poll threads list every 4 seconds

        // Search threads logic
        searchInput.addEventListener('input', function() {
            filterThreads(this.value.toLowerCase());
        });

        // Load Threads from Server
        function loadThreads() {
            fetch("{{ route('admin.cs-chats.threads') }}", {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    allThreads = data.threads;
                    renderThreads(allThreads);
                }
            })
            .catch(err => console.error("Error loading chat threads:", err));
        }

        // Render Threads in left pane
        function renderThreads(threads) {
            const query = searchInput.value.toLowerCase();
            const filtered = threads.filter(t => t.name.toLowerCase().includes(query) || t.email.toLowerCase().includes(query));

            threadsList.innerHTML = '';
            
            if (filtered.length === 0) {
                threadsList.innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-user-slash fa-2x mb-2 d-block text-opacity-50"></i>
                        Tidak ada percakapan ditemukan.
                    </div>
                `;
                return;
            }

            filtered.forEach(t => {
                const isActive = t.user_id === activeUserId;
                const unreadBadgeHtml = t.unread_count > 0 
                    ? `<span class="badge bg-danger rounded-pill px-2 py-1">${t.unread_count}</span>` 
                    : '';

                const threadHtml = `
                    <div class="thread-item p-3 d-flex justify-content-between align-items-center ${isActive ? 'active' : ''}" data-user-id="${t.user_id}">
                        <div class="d-flex align-items-center gap-3" style="max-width: 80%;">
                            <img src="${getAvatarSvg(t.name)}" class="rounded-circle" style="width: 40px; height: 40px;">
                            <div style="min-width: 0;">
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0 text-truncate" style="font-size: 0.9rem; max-width: 140px;" title="${t.name}">${t.name}</h6>
                                    <span class="badge ${t.badge_class}" style="font-size: 0.65rem; padding: 3px 6px;">${t.tag}</span>
                                </div>
                                <small class="text-muted text-truncate d-block mt-0.5" style="font-size: 0.78rem; max-width: 190px;">${t.latest_message || 'Tidak ada pesan'}</small>
                            </div>
                        </div>
                        <div class="d-flex flex-column align-items-end gap-1">
                            <small class="text-muted" style="font-size: 0.7rem;">${t.time_formatted}</small>
                            ${unreadBadgeHtml}
                        </div>
                    </div>
                `;
                threadsList.insertAdjacentHTML('beforeend', threadHtml);
            });

            // Bind click events
            document.querySelectorAll('.thread-item').forEach(item => {
                item.addEventListener('click', function() {
                    const userId = parseInt(this.getAttribute('data-user-id'));
                    selectThread(userId);
                });
            });
        }

        // Filter threads locally
        function filterThreads(query) {
            renderThreads(allThreads);
        }

        // Select Thread and display messages
        function selectThread(userId) {
            activeUserId = userId;
            
            // Mark active class visually
            document.querySelectorAll('.thread-item').forEach(item => {
                if (parseInt(item.getAttribute('data-user-id')) === userId) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });

            placeholderCard.style.setProperty('display', 'none', 'important');
            activeChatCard.style.setProperty('display', 'flex', 'important');
            
            // Load messages initially
            loadMessages(userId, true);
            
            // Clear existing polling and start new one
            if (pollingInterval) {
                clearInterval(pollingInterval);
            }
            pollingInterval = setInterval(() => {
                loadMessages(userId, false);
            }, 3000);
        }

        // Load Messages from Server
        let messagesLength = 0;
        function loadMessages(userId, forceScroll) {
            fetch("{{ route('admin.cs-chats.messages', ':id') }}".replace(':id', userId), {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && activeUserId === userId) {
                    // Update header & sidebar details
                    chatClientName.textContent = data.client.name;
                    chatClientEmail.textContent = data.client.email;
                    chatClientBadge.textContent = data.client.tag;
                    chatClientBadge.className = 'badge ' + (data.client.tag === 'B2B Reseller' ? 'bg-primary' : 'bg-success');
                    chatClientAvatar.src = getAvatarSvg(data.client.name);
                    
                    sideClientName.textContent = data.client.name;
                    sideClientEmail.textContent = data.client.email;
                    sideClientPhone.textContent = data.client.phone;
                    sideClientAddress.textContent = data.client.address;
                    sideClientTag.textContent = data.client.tag;
                    sideClientTag.className = 'badge ' + (data.client.tag === 'B2B Reseller' ? 'bg-primary' : 'bg-success');

                    // Check if new messages arrived
                    const hasNew = data.messages.length > messagesLength || forceScroll;
                    messagesLength = data.messages.length;

                    if (hasNew) {
                        messagesContainer.innerHTML = '';
                        
                        data.messages.forEach(msg => {
                            // Check if sender is admin (sender_id != client id)
                            const isAdmin = msg.sender_id != userId;
                            const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                            const bubbleHtml = `
                                <div class="d-flex flex-column ${isAdmin ? 'align-items-end' : 'align-items-start'}">
                                    <div class="chat-bubble ${isAdmin ? 'chat-bubble-sent' : 'chat-bubble-received'}">
                                        ${escapeHtml(msg.message)}
                                        <span class="chat-time ${isAdmin ? 'text-white-50' : 'text-muted'}">${time}</span>
                                    </div>
                                </div>
                            `;
                            messagesContainer.insertAdjacentHTML('beforeend', bubbleHtml);
                        });
                        
                        // Scroll to bottom
                        messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    }
                }
            })
            .catch(err => console.error("Error loading messages:", err));
        }

        // Send reply form submit
        adminChatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!activeUserId) return;

            const text = adminChatInput.value.trim();
            if (!text) return;

            adminChatInput.value = '';

            fetch("{{ route('admin.cs-chats.send', ':id') }}".replace(':id', activeUserId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ message: text })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadMessages(activeUserId, true);
                    loadThreads();
                }
            })
            .catch(err => {
                console.error("Error sending admin reply:", err);
                alert("Gagal mengirim balasan.");
            });
        });

        // Helper: Escape HTML string to prevent XSS
        function escapeHtml(str) {
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    });
</script>
@endpush
