@php
    $allowed = auth()->check() && (
        auth()->user()->role === 'customer' || 
        (auth()->user()->role === 'branch' && auth()->user()->b2b_type === 'reseller')
    );
@endphp

@if($allowed)
<div id="cs-chat-widget-container" style="position: fixed; bottom: 30px; right: 30px; z-index: 9999; font-family: 'Outfit', sans-serif;">
    
    {{-- Floating Bubble Button --}}
    <button id="cs-chat-bubble" class="btn btn-primary d-flex align-items-center justify-content-center shadow-lg position-relative" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #0d6efd, #0043a8); border: none; transition: all 0.3s ease-in-out;">
        <i class="fas fa-comments fs-3 text-white"></i>
        <span id="cs-chat-unread-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="display: none; font-size: 0.75rem; padding: 0.35em 0.65em;">
            0
        </span>
    </button>

    {{-- Chat Box Panel --}}
    <div id="cs-chat-box" class="card shadow-2xl border-0 overflow-hidden" style="display: none; position: absolute; bottom: 80px; right: 0; width: 380px; height: 500px; border-radius: 20px; transition: all 0.3s ease-in-out; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.25);">
        
        {{-- Header --}}
        <div class="card-header d-flex align-items-center justify-content-between p-3" style="background: linear-gradient(135deg, #0d6efd, #0b5ed7); color: white;">
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative">
                    @php
                        $csSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100%" height="100%"><circle cx="50" cy="50" r="50" fill="#ffffff"/><text x="50%" y="55%" dominant-baseline="middle" text-anchor="middle" font-size="45" font-family="\'Poppins\', sans-serif" font-weight="bold" fill="#0d6efd">CS</text></svg>';
                        $csAvatar = 'data:image/svg+xml;utf8,' . rawurlencode($csSvg);
                    @endphp
                    <img src="{{ $csAvatar }}" alt="CS" class="rounded-circle" style="width: 40px; height: 40px;">
                    <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" style="width: 10px; height: 10px; border-width: 2px;"></span>
                </div>
                <div>
                    <h6 class="fw-bold mb-0" style="font-size: 0.95rem;">Customer Service CS</h6>
                    <small class="text-white text-opacity-75" style="font-size: 0.75rem;"><i class="fas fa-circle text-success-light me-1"></i>Online | Siap Membantu</small>
                </div>
            </div>
            <button id="cs-chat-close" class="btn-close btn-close-white" aria-label="Close"></button>
        </div>

        {{-- Messages Body --}}
        <div id="cs-chat-messages" class="card-body p-3 overflow-y-auto" style="height: calc(100% - 135px); background: #f8f9fa;">
            <div class="text-center text-muted my-3 small p-2 rounded bg-white shadow-sm border">
                <i class="fas fa-lock me-1"></i> Percakapan ini aman dan terenkripsi. CS kami akan segera menjawab.
            </div>
            <div id="cs-messages-container" class="d-flex flex-column gap-2">
                {{-- Chat messages get appended here --}}
            </div>
        </div>

        {{-- Footer Input --}}
        <div class="card-footer p-2 bg-white border-top">
            <form id="cs-chat-form" class="d-flex gap-2">
                <input type="text" id="cs-chat-input" class="form-control rounded-pill border-0 px-3" placeholder="Tulis pesan Anda..." style="background: #f1f3f5; font-size: 0.9rem;" autocomplete="off" required>
                <button type="submit" class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: #0d6efd; border: none;">
                    <i class="fas fa-paper-plane text-white" style="font-size: 0.9rem;"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    /* Styling scrollbar chat widget */
    #cs-chat-messages::-webkit-scrollbar {
        width: 6px;
    }
    #cs-chat-messages::-webkit-scrollbar-track {
        background: transparent;
    }
    #cs-chat-messages::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }
    #cs-chat-bubble:hover {
        transform: scale(1.1);
        box-shadow: 0 10px 20px rgba(13, 110, 253, 0.4) !important;
    }
    .cs-msg-bubble {
        max-width: 75%;
        padding: 10px 14px;
        border-radius: 16px;
        font-size: 0.88rem;
        line-height: 1.4;
        word-wrap: break-word;
    }
    .cs-msg-sent {
        align-self: flex-end;
        background-color: #0d6efd;
        color: white;
        border-bottom-right-radius: 4px;
    }
    .cs-msg-received {
        align-self: flex-start;
        background-color: white;
        color: #212529;
        border-bottom-left-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        border: 1px solid #e9ecef;
    }
    .cs-msg-time {
        font-size: 0.65rem;
        display: block;
        margin-top: 4px;
        opacity: 0.75;
    }
    .text-success-light {
        color: #2ecc71;
    }
    /* Pulse animation for unread badge */
    #cs-chat-unread-badge {
        animation: csBadgePulse 1.5s ease-in-out infinite;
    }
    @keyframes csBadgePulse {
        0%, 100% { transform: translate(-50%, -50%) scale(1); }
        50% { transform: translate(-50%, -50%) scale(1.2); }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const bubble = document.getElementById('cs-chat-bubble');
        const chatBox = document.getElementById('cs-chat-box');
        const closeBtn = document.getElementById('cs-chat-close');
        const chatForm = document.getElementById('cs-chat-form');
        const chatInput = document.getElementById('cs-chat-input');
        const msgContainer = document.getElementById('cs-messages-container');
        const unreadBadge = document.getElementById('cs-chat-unread-badge');
        
        let isOpen = false;
        let pollInterval = null;
        let latestMsgCount = 0;

        // Toggle Chat Window
        bubble.addEventListener('click', function() {
            isOpen = !isOpen;
            if (isOpen) {
                chatBox.style.display = 'block';
                // Slide up animation effect
                chatBox.style.opacity = '0';
                chatBox.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    chatBox.style.opacity = '1';
                    chatBox.style.transform = 'translateY(0)';
                }, 50);
                
                // Hide badge immediately when opened
                unreadBadge.style.display = 'none';
                
                fetchMessages(true); // mark as read when opening
                // Start polling every 3 seconds when open (mark read each time)
                pollInterval = setInterval(() => fetchMessages(true), 3000);
            } else {
                closeChat();
            }
        });

        closeBtn.addEventListener('click', closeChat);

        function closeChat() {
            isOpen = false;
            chatBox.style.opacity = '0';
            chatBox.style.transform = 'translateY(20px)';
            setTimeout(() => {
                chatBox.style.display = 'none';
            }, 300);
            
            if (pollInterval) {
                clearInterval(pollInterval);
                pollInterval = null;
            }
        }

        // Fetch Messages from Server
        // markRead: if true, tells server to mark admin replies as read
        function fetchMessages(markRead = false) {
            let url = "{{ route('chat.fetch') }}";
            if (markRead) {
                url += (url.includes('?') ? '&' : '?') + 'mark_read=1';
            }

            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderMessages(data.messages);
                }
            })
            .catch(err => console.error("Error fetching CS chat messages:", err));
        }

        // Render Message Bubbles
        function renderMessages(messages) {
            // Count total messages
            const currentCount = messages.length;
            
            // Check if we need to update/scroll (new messages arrived)
            let hasNew = currentCount > latestMsgCount;
            latestMsgCount = currentCount;

            // Compute unread count for badge (admin replies not read)
            let unread = 0;
            messages.forEach(msg => {
                // If message is sent by admin (sender_id != current user) and is_read is false
                if (msg.sender_id != "{{ auth()->id() }}" && !msg.is_read) {
                    unread++;
                }
            });

            if (unread > 0 && !isOpen) {
                unreadBadge.textContent = unread;
                unreadBadge.style.display = 'block';
            } else {
                unreadBadge.style.display = 'none';
            }

            if (hasNew) {
                msgContainer.innerHTML = '';
                
                if (messages.length === 0) {
                    msgContainer.innerHTML = `
                        <div class="text-center text-muted my-4 py-3" id="no-chats-placeholder">
                            <i class="fas fa-comments fa-2x mb-2 d-block text-opacity-50"></i>
                            Halo! Ada yang bisa kami bantu? Silakan kirim pesan di bawah ini.
                        </div>
                    `;
                } else {
                    messages.forEach(msg => {
                        const isSent = msg.sender_id == "{{ auth()->id() }}";
                        const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        
                        const msgHtml = `
                            <div class="cs-msg-bubble ${isSent ? 'cs-msg-sent' : 'cs-msg-received'}">
                                ${escapeHtml(msg.message)}
                                <span class="cs-msg-time ${isSent ? 'text-white-50' : 'text-muted'}">${time}</span>
                            </div>
                        `;
                        msgContainer.insertAdjacentHTML('beforeend', msgHtml);
                    });
                }
                
                // Scroll to bottom
                const messagesBody = document.getElementById('cs-chat-messages');
                messagesBody.scrollTop = messagesBody.scrollHeight;
            }
        }

        // Send Message
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const text = chatInput.value.trim();
            if (!text) return;

            // Clear input
            chatInput.value = '';

            // Send to backend via AJAX
            fetch("{{ route('chat.send') }}", {
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
                    fetchMessages();
                }
            })
            .catch(err => {
                console.error("Error sending message:", err);
                alert("Gagal mengirim pesan. Silakan coba lagi.");
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

        // Check for unread messages immediately on page load
        fetchMessages(false);

        // Run badge check periodically without opening
        setInterval(function() {
            if (!isOpen) {
                fetchMessages(false);
            }
        }, 8000); // Poll every 8 seconds when widget is closed
    });
</script>
@endif
