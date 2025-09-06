@extends(backpack_view('blank'))

@section('content')
<div class="row">
    <div class="col-md-12">
        @if(isset($error))
            <div class="alert alert-danger">
                <i class="la la-exclamation-triangle"></i> {{ $error }}
            </div>
        @endif
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="la la-comments"></i> Support Conversations
                </h3>
                <div class="card-tools">
                    <span class="badge badge-primary" id="total-conversations">0</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="conversations-list" id="conversations-container">
                    <!-- Conversations will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Conversation Template -->
<template id="conversation-template">
    <div class="conversation-item border-bottom p-3" data-user-id="">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0 avatar-container">
                <div class="user-avatar-placeholder rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 60px; height: 60px; background-color: #6c757d; color: white; font-size: 24px;">
                    <i class="la la-user"></i>
                </div>
                <img src="" alt="User Avatar" class="rounded-circle user-avatar-image" width="60" height="60" 
                     style="display: none; position: absolute; top: 0; left: 0;">
            </div>
            <div class="flex-grow-1 conversation-content">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="conversation-info">
                        <h6 class="mb-2 user-name"></h6>
                        <p class="mb-1 last-message"></p>
                        <div class="conversation-meta">
                            <small class="last-message-time"></small>
                            <span class="user-mobile"></span>
                        </div>
                    </div>
                    <div class="conversation-actions">
                        <span class="badge badge-danger unread-count" style="display: none;"></span>
                        <button class="btn btn-sm btn-outline-primary open-conversation">
                            <i class="la la-comment"></i> Chat
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

@endsection

@push('after_styles')
<style>
/* Page-level styling */
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px 0;
    margin-bottom: 30px;
    border-radius: 0 0 20px 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.page-header h1 {
    margin: 0;
    font-weight: 700;
    font-size: 2.5rem;
}

.page-header .subtitle {
    opacity: 0.9;
    font-size: 1.1rem;
    margin-top: 8px;
}

/* Container styling */
.conversations-container {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 20px;
}

/* Stats cards */
.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 24px;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    text-align: center;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: #007bff;
    margin-bottom: 8px;
}

.stat-label {
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.9rem;
}
.conversations-list {
    max-height: 600px;
    overflow-y: auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.conversation-item {
    transition: all 0.3s ease;
    cursor: pointer;
    padding: 20px 24px;
    border-bottom: 1px solid #f0f0f0;
    position: relative;
    overflow: hidden;
}

.conversation-item:hover {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
}

.conversation-item:last-child {
    border-bottom: none !important;
}

.conversation-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(180deg, #007bff 0%, #0056b3 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.conversation-item:hover::before {
    opacity: 1;
}

.avatar-container {
    position: relative;
    width: 60px;
    height: 60px;
    margin-right: 20px;
}

.conversation-content {
    flex: 1;
    min-width: 0;
}

.conversation-info {
    flex: 1;
    min-width: 0;
}

.conversation-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
    margin-left: 16px;
}

.user-name {
    color: #2c3e50;
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 8px;
    line-height: 1.3;
}

.last-message {
    font-size: 0.95rem;
    line-height: 1.4;
    color: #6c757d;
    margin-bottom: 8px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.conversation-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    font-size: 0.85rem;
}

.last-message-time {
    color: #95a5a6;
    font-weight: 500;
}

.user-mobile {
    color: #7f8c8d;
    font-weight: 400;
}

.unread-count {
    display: inline-block;
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
    font-weight: 600;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
}

.open-conversation {
    font-size: 0.85rem;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: 2px solid #007bff;
    background: transparent;
    color: #007bff;
}

.open-conversation:hover {
    background: #007bff;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}

.open-conversation:active {
    transform: translateY(0);
}

.avatar-container {
    position: relative;
    width: 50px;
    height: 50px;
}

.conversation-item img,
.conversation-item .user-avatar-placeholder {
    object-fit: cover;
    border: 3px solid #e9ecef;
    transition: all 0.3s ease;
    width: 60px;
    height: 60px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.conversation-item img:not([src*="avatar.png"]) {
    border-color: #007bff;
    box-shadow: 0 4px 16px rgba(0, 123, 255, 0.2);
}

.user-avatar-placeholder {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
    color: white !important;
    font-size: 24px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    position: absolute;
    top: 0;
    left: 0;
    box-shadow: 0 4px 16px rgba(108, 117, 125, 0.3);
}

.user-avatar-placeholder:hover {
    background: linear-gradient(135deg, #5a6268 0%, #343a40 100%) !important;
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
}

.user-avatar-image {
    object-fit: cover;
    position: absolute;
    top: 0;
    left: 0;
}

.user-avatar-image:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
}
</style>
@endpush

@push('after_scripts')
<script>
$(document).ready(function() {
    // Ensure initial state is correct
    initializeAvatarStates();
    
    loadConversations();
    
    // Refresh conversations every 5 seconds
    setInterval(loadConversations, 5000);
});

function initializeAvatarStates() {
    // Hide all images initially, show all placeholders
    $('.user-avatar-image').hide();
    $('.user-avatar-placeholder').show();
}

function loadConversations() {
    console.log('Loading conversations...');
    
    $.ajax({
        url: '{{ route("backpack.message.conversations") }}',
        method: 'GET',
        success: function(response) {
            console.log('Response received:', response);
            
            // Handle both direct array response and JSON response
            let conversations = [];
            if (response && response.conversations) {
                conversations = response.conversations;
                console.log('Using response.conversations:', conversations);
            } else if (Array.isArray(response)) {
                conversations = response;
                console.log('Using direct array response:', conversations);
            } else {
                console.error('Unexpected response format:', response);
                showAlert('Invalid response format from server', 'error');
                return;
            }
            
            console.log('Final conversations array:', conversations);
            displayConversations(conversations);
            updateTotalCount(conversations.length);
        },
        error: function(xhr) {
            console.error('Error loading conversations:', xhr);
            showAlert('Error loading conversations', 'error');
            
            // Show error in the container
            const container = $('#conversations-container');
            container.html(`
                <div class="text-center p-5">
                    <i class="la la-exclamation-triangle" style="font-size: 3em; color: #dc3545;"></i>
                    <p class="mt-3 text-danger">Failed to load conversations</p>
                    <button class="btn btn-primary" onclick="loadConversations()">
                        <i class="la la-refresh"></i> Try Again
                    </button>
                </div>
            `);
        }
    });
}

function displayConversations(conversations) {
    console.log('Displaying conversations:', conversations);
    
    const container = $('#conversations-container');
    const template = document.getElementById('conversation-template');
    
    // Check if template exists
    if (!template) {
        console.error('Conversation template not found');
        container.html(`
            <div class="text-center p-5">
                <i class="la la-exclamation-triangle" style="font-size: 3em; color: #dc3545;"></i>
                <p class="mt-3 text-danger">Template error: Conversation template not found</p>
            </div>
        `);
        return;
    }
    
    container.empty();
    
    if (!conversations || conversations.length === 0) {
        console.log('No conversations to display');
        container.html(`
            <div class="text-center p-5">
                <i class="la la-comments-o" style="font-size: 3em; color: #ccc;"></i>
                <p class="mt-3 text-muted">No conversations yet</p>
            </div>
        `);
        return;
    }
    
    console.log('Processing', conversations.length, 'conversations');
    
    conversations.forEach(function(conversation, index) {
        try {
            console.log('Processing conversation', index, conversation);
            
            // Validate conversation object
            if (!conversation || typeof conversation !== 'object') {
                console.warn('Invalid conversation object at index', index, conversation);
                return;
            }
            
            const clone = template.content.cloneNode(true);
            
            // Set user data with fallbacks
            clone.querySelector('.conversation-item').setAttribute('data-user-id', conversation.user_id || 'unknown');
            
            // Handle image with better fallback logic
            const imgElement = clone.querySelector('.user-avatar-image');
            const placeholderElement = clone.querySelector('.user-avatar-placeholder');
            
            console.log('Avatar elements found:', { imgElement: !!imgElement, placeholderElement: !!placeholderElement });
            
            if (conversation.image && conversation.image.trim() !== '' && !conversation.image.startsWith('http')) {
                // Local image path - show image, hide placeholder
                console.log('Setting image:', conversation.image);
                imgElement.src = conversation.image;
                imgElement.style.display = 'block';
                placeholderElement.style.display = 'none';
                
                // Add error handling for image load
                imgElement.onerror = function() {
                    console.log('Image failed to load, showing placeholder');
                    this.style.display = 'none';
                    placeholderElement.style.display = 'flex';
                };
                
                imgElement.onload = function() {
                    console.log('Image loaded successfully');
                    this.style.display = 'block';
                    placeholderElement.style.display = 'none';
                };
            } else {
                // No image - show placeholder, hide image
                console.log('No valid image, showing placeholder');
                imgElement.style.display = 'none';
                placeholderElement.style.display = 'flex';
            }
            
            clone.querySelector('.user-name').textContent = conversation.name || 'Unknown User';
            clone.querySelector('.last-message').textContent = conversation.last_message || 'No message content';
            clone.querySelector('.last-message-time').textContent = formatTime(conversation.last_message_time || new Date());
            
            // Set mobile number if available
            const mobileElement = clone.querySelector('.user-mobile');
            if (conversation.mobile) {
                mobileElement.textContent = conversation.mobile;
                mobileElement.style.display = 'inline';
            } else {
                mobileElement.style.display = 'none';
            }
            
            // Show unread count if any
            if (conversation.unread_count && conversation.unread_count > 0) {
                const unreadBadge = clone.querySelector('.unread-count');
                unreadBadge.textContent = conversation.unread_count;
                unreadBadge.style.display = 'inline-block';
            }
            
            // Add click handler
            clone.querySelector('.open-conversation').addEventListener('click', function() {
                openConversation(conversation.user_id, conversation.name);
            });
            
            container.append(clone);
        } catch (error) {
            console.error('Error processing conversation at index', index, error, conversation);
        }
    });
}

function openConversation(userId, userName) {
    const url = '{{ route("backpack.message.conversation", ":userId") }}'.replace(':userId', userId);
    const newWindow = window.open(url, '_blank');
    
    // Listen for the window to close and refresh conversations
    const checkClosed = setInterval(function() {
        if (newWindow.closed) {
            clearInterval(checkClosed);
            // Refresh conversations list to update unread counts
            loadConversations();
        }
    }, 1000);
}

function updateTotalCount(count) {
    $('#total-conversations').text(count);
}

function formatTime(timestamp) {
    try {
        const date = new Date(timestamp);
        
        // Check if date is valid
        if (isNaN(date.getTime())) {
            return 'Invalid date';
        }
        
        const now = new Date();
        const diffInHours = (now - date) / (1000 * 60 * 60);
        
        if (diffInHours < 24) {
            return date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        } else if (diffInHours < 48) {
            return 'Yesterday';
        } else {
            return date.toLocaleDateString();
        }
    } catch (error) {
        console.error('Error formatting timestamp:', timestamp, error);
        return 'Invalid date';
    }
}

function showAlert(message, type = 'info') {
    // You can implement your own alert system here
    console.log(`${type.toUpperCase()}: ${message}`);
}
</script>
@endpush 