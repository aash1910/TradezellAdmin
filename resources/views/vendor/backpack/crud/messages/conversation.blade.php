@extends(backpack_view('blank'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('backpack.message.conversations') }}" class="btn btn-sm btn-outline-secondary me-4">
                            <i class="la la-arrow-left"></i> Back
                        </a>
                        <div class="user-avatar-container me-4">
                            @if($user->image && !str_starts_with($user->image, 'http'))
                                <img src="{{ asset($user->image) }}" 
                                     alt="User Avatar" class="rounded-circle user-avatar-image" width="50" height="50">
                            @else
                                <div class="user-avatar-placeholder rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="la la-user"></i>
                                </div>
                            @endif
                        </div>
                        <div class="user-info">
                            <h5 class="mb-0 user-name">{{ $user->full_name }}</h5>
                            <div class="user-details">
                                @if($user->mobile)
                                    <span class="user-detail-item">
                                        <i class="la la-phone"></i> {{ $user->mobile }}
                                    </span>
                                @endif
                                @if($user->email)
                                    <span class="user-detail-item">
                                        <i class="la la-envelope"></i> {{ $user->email }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge badge-info">User ID: {{ $user->id }}</span>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0">
                <!-- Messages Container -->
                <div class="messages-container" id="messages-container">
                    @foreach($messages as $message)
                        <div class="message-item {{ $message->sender_id == 1 ? 'message-admin' : 'message-user' }}">
                            <div class="message-content">
                                <div class="message-bubble">
                                    <p class="mb-1">{{ $message->message }}</p>
                                    <small class="text-muted">{{ $message->created_at->format('M j, g:i A') }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Message Input -->
                <div class="message-input-container p-3 border-top">
                    <form id="message-form">
                        <div class="input-group">
                            <input type="text" class="form-control" id="message-input" 
                                   placeholder="Type your message..." maxlength="1000" required>
                            <button class="btn btn-primary" type="submit" id="send-button">
                                <i class="la la-paper-plane"></i> Send
                            </button>
                        </div>
                        <small class="text-muted">
                            <span id="char-count">0</span> / 1000 characters
                        </small>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('after_styles')
<style>
/* Header styling */
.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px 12px 0 0;
    padding: 32px 24px;
}

/* Force proper spacing in header */
.card-header .d-flex.align-items-center {
    gap: 15px; /* Force gap between all elements */
}

.card-header .d-flex.align-items-center > * {
    flex-shrink: 0; /* Prevent elements from shrinking */
}

/* Back button styling */
.btn-outline-secondary {
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: rgba(255, 255, 255, 0.9);
    background: transparent;
    padding: 8px 16px;
    font-weight: 600;
    border-radius: 20px;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.btn-outline-secondary:hover {
    border-color: rgba(255, 255, 255, 0.6);
    color: white;
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-1px);
    box-shadow: 0 4px 16px rgba(255, 255, 255, 0.2);
}

.btn-outline-secondary:active {
    transform: translateY(0);
}

.user-avatar-container {
    position: relative;
    width: 50px;
    height: 50px;
}

.user-avatar-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border: 3px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.user-avatar-image:hover {
    transform: scale(1.05);
    border-color: rgba(255, 255, 255, 0.5);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

.user-avatar-placeholder {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
    color: rgba(255, 255, 255, 0.9);
    font-size: 20px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.user-avatar-placeholder:hover {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0.2) 100%);
    transform: scale(1.05);
}

.user-info {
    flex: 1;
}

.user-name {
    color: white;
    font-weight: 700;
    font-size: 1.4rem;
    margin-bottom: 12px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    letter-spacing: 0.5px;
}

.user-details {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.user-detail-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.95rem;
    font-weight: 500;
    padding: 6px 12px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.user-detail-item:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-1px);
}

.user-detail-item i {
    opacity: 0.9;
    font-size: 1rem;
}

/* Card styling */
.card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.messages-container {
    height: 500px;
    overflow-y: auto;
    padding: 24px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.message-item {
    margin-bottom: 24px;
    display: flex;
    animation: fadeInUp 0.3s ease;
}

.message-admin {
    justify-content: flex-end;
}

.message-user {
    justify-content: flex-start;
}

.message-content {
    max-width: 70%;
    position: relative;
}

.message-bubble {
    padding: 16px 20px;
    border-radius: 20px;
    position: relative;
    word-wrap: break-word;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.message-bubble:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.message-admin .message-bubble {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    border-bottom-right-radius: 6px;
    margin-left: 20%;
}

.message-user .message-bubble {
    background: white;
    color: #2c3e50;
    border: none;
    border-bottom-left-radius: 6px;
    margin-right: 20%;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

.message-bubble p {
    margin: 0;
    line-height: 1.5;
    font-size: 0.95rem;
    font-weight: 400;
}

.message-bubble small {
    opacity: 0.8;
    font-size: 0.75rem;
    font-weight: 500;
    margin-top: 8px;
    display: block;
}

/* Message animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message-input-container {
    background: white;
    padding: 24px;
    border-top: 1px solid #f0f0f0;
    position: relative;
    margin-bottom: 10px;
}

.message-input-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent 0%, #e9ecef 50%, transparent 100%);
}

#message-input {
    border-radius: 25px 0 0 25px;
    border: 2px solid #e9ecef;
    padding: 25px 20px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

#message-input:focus {
    border-color: #007bff;
    box-shadow: 0 4px 16px rgba(0, 123, 255, 0.15);
    outline: none;
}

#send-button {
    border-radius: 0 25px 25px 0;
    border: 2px solid #007bff;
    background: #007bff;
    color: white;
    padding: 12px 24px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.2);
}

#send-button:hover {
    background: #0056b3;
    border-color: #0056b3;
    transform: translateY(-1px);
    box-shadow: 0 4px 16px rgba(0, 123, 255, 0.3);
}

#send-button:active {
    transform: translateY(0);
}

#char-count {
    color: #6c757d;
    font-size: 0.85rem;
    font-weight: 500;
    margin-top: 8px;
}

#char-count.text-warning {
    color: #ffc107;
    font-weight: 600;
}

/* Scrollbar styling */
.messages-container::-webkit-scrollbar {
    width: 8px;
}

.messages-container::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.05);
    border-radius: 4px;
}

.messages-container::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, #007bff 0%, #0056b3 100%);
    border-radius: 4px;
    border: 2px solid rgba(255, 255, 255, 0.1);
}

.messages-container::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, #0056b3 0%, #004085 100%);
}

/* Loading animation */
.loading {
    opacity: 0.7;
    pointer-events: none;
    position: relative;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 24px;
    height: 24px;
    margin: -12px 0 0 -12px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-top: 3px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Enhanced button states */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Responsive design */
@media (max-width: 768px) {
    .card-header {
        padding: 20px 16px;
    }
    
    .btn-outline-secondary {
        padding: 6px 12px;
        font-size: 0.85rem;
    }
    
    .user-avatar-container {
        margin-right: 16px !important;
    }
    
    .user-name {
        font-size: 1.2rem;
        margin-bottom: 10px;
    }
    
    .user-details {
        flex-direction: column;
        gap: 12px;
    }
    
    .user-detail-item {
        padding: 5px 10px;
        font-size: 0.9rem;
    }
    
    .message-bubble {
        padding: 12px 16px;
    }
    
    .message-input-container {
        padding: 16px;
    }
}

@media (max-width: 576px) {
    .card-header {
        padding: 16px 12px;
    }
    
    .btn-outline-secondary {
        padding: 5px 10px;
        font-size: 0.8rem;
    }
    
    .user-avatar-container {
        margin-right: 12px !important;
    }
    
    .user-name {
        font-size: 1.1rem;
    }
    
    .user-details {
        gap: 8px;
    }
}
</style>
@endpush

@push('after_scripts')
<script>
$(document).ready(function() {
    // Scroll to bottom of messages
    scrollToBottom();
    
    // Auto-resize textarea (if you want to use textarea instead of input)
    // $('#message-input').on('input', function() {
    //     this.style.height = 'auto';
    //     this.style.height = (this.scrollHeight) + 'px';
    // });
    
    // Character count
    $('#message-input').on('input', function() {
        const count = $(this).val().length;
        $('#char-count').text(count);
        
        if (count > 900) {
            $('#char-count').addClass('text-warning');
        } else {
            $('#char-count').removeClass('text-warning');
        }
    });
    
    // Handle form submission
    $('#message-form').on('submit', function(e) {
        e.preventDefault();
        sendMessage();
    });
    
    // Auto-refresh messages every 10 seconds
    setInterval(refreshMessages, 5000);
});

function sendMessage() {
    const messageInput = $('#message-input');
    const message = messageInput.val().trim();
    const sendButton = $('#send-button');
    
    if (!message) return;
    
    // Disable input and button
    messageInput.prop('disabled', true);
    sendButton.prop('disabled', true).addClass('loading');
    
    $.ajax({
        url: '{{ route("backpack.message.send") }}',
        method: 'POST',
        data: {
            receiver_id: {{ $user->id }},
            message: message,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.status === 'success') {
                // Add message to UI
                addMessageToUI(response.message);
                
                // Clear input
                messageInput.val('');
                $('#char-count').text('0');
                
                // Scroll to bottom
                scrollToBottom();
                
                // Show success message
                showAlert('Message sent successfully!', 'success');
            }
        },
        error: function(xhr) {
            console.error('Error sending message:', xhr);
            showAlert('Error sending message. Please try again.', 'error');
        },
        complete: function() {
            // Re-enable input and button
            messageInput.prop('disabled', false).focus();
            sendButton.prop('disabled', false).removeClass('loading');
        }
    });
}

function addMessageToUI(messageData) {
    const isAdmin = messageData.sender_id == 1;
    const messageHtml = `
        <div class="message-item ${isAdmin ? 'message-admin' : 'message-user'}">
            <div class="message-content">
                <div class="message-bubble">
                    <p class="mb-1">${messageData.message}</p>
                    <small class="text-muted">${formatTime(messageData.created_at)}</small>
                </div>
            </div>
        </div>
    `;
    
    $('#messages-container').append(messageHtml);
}

function refreshMessages() {
    $.ajax({
        url: '{{ route("backpack.message.messages", $user->id) }}',
        method: 'GET',
        success: function(response) {
            //if (response.status === 'success') {
                updateMessagesUI(response);
            //}
        },
        error: function(xhr) {
            console.error('Error refreshing messages:', xhr);
        }
    });
}

function updateMessagesUI(messages) {
    const container = $('#messages-container');
    const currentCount = container.find('.message-item').length;
    
    if (messages.length > currentCount) {
        // Only add new messages
        for (let i = currentCount; i < messages.length; i++) {
            const message = messages[i];
            const isAdmin = message.sender_id == 1;
            const messageHtml = `
                <div class="message-item ${isAdmin ? 'message-admin' : 'message-user'}">
                    <div class="message-content">
                        <div class="message-bubble">
                            <p class="mb-1">${message.message}</p>
                            <small class="text-muted">${formatTime(message.created_at)}</small>
                        </div>
                    </div>
                </div>
            `;
            
            container.append(messageHtml);
        }
        
        // Scroll to bottom if user is at bottom
        //if (isAtBottom()) {
            scrollToBottom();
        //}
    }
}

function scrollToBottom() {
    const container = $('#messages-container');
    container.scrollTop(container[0].scrollHeight);
}

function isAtBottom() {
    const container = $('#messages-container');
    const threshold = 50; // pixels from bottom
    return container.scrollTop() + container.height() >= container[0].scrollHeight - threshold;
}

function formatTime(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

function showAlert(message, type = 'info') {
    console.log(`${type.toUpperCase()}: ${message}`);
    
    // Enhanced toast notification
    const alertClass = type === 'error' ? 'danger' : type;
    const iconClass = type === 'success' ? 'la-check-circle' : 
                     type === 'error' ? 'la-exclamation-circle' : 
                     type === 'warning' ? 'la-exclamation-triangle' : 'la-info-circle';
    
    const alertHtml = `
        <div class="alert alert-${alertClass} alert-dismissible fade show position-fixed custom-alert" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 350px; border: none; border-radius: 12px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);">
            <div class="d-flex align-items-center">
                <i class="la ${iconClass} me-2" style="font-size: 1.2rem;"></i>
                <span class="flex-grow-1">${message}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" style="opacity: 0.7;"></button>
            </div>
        </div>
    `;
    
    $('body').append(alertHtml);
    
    // Auto-remove after 5 seconds
    setTimeout(function() {
        $('.custom-alert').fadeOut(500, function() {
            $(this).remove();
        });
    }, 5000);
}
</script>
@endpush 