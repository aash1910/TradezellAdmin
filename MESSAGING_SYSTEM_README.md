# PiqDrop Admin Messaging System

## Overview

This messaging system allows Support Service/Super Admin (user_id = 1) to communicate with both senders and droppers/riders through the admin panel. The system provides a complete chat interface where support staff can view conversations and send messages to users.

## Features

### 🔐 **Access Control**
- Only Support Service/Super Admin (user_id = 1) can access the messaging system
- Regular admin users cannot access messaging functionality
- Secure middleware protection on all messaging routes

### 💬 **Conversation Management**
- View all conversations with users
- See last message and timestamp for each conversation
- Unread message count indicators
- Real-time conversation updates

### 📱 **Chat Interface**
- Modern chat-like interface similar to mobile apps
- Message bubbles with different styles for admin vs user messages
- Real-time message sending and receiving
- Auto-refresh conversations every 30 seconds
- Auto-refresh individual conversations every 10 seconds

### 📊 **Dashboard Integration**
- Recent conversations widget on admin dashboard
- Quick access to messaging system
- Visual indicators for unread messages

## Installation & Setup

### 1. **Database Migration**
Run the migration to add the `is_read` column to the messages table:

```bash
php artisan migrate
```

### 2. **File Structure**
The following files have been created/modified:

```
app/
├── Http/Controllers/Admin/
│   └── MessageCrudController.php          # Admin messaging controller
├── Models/
│   └── Message.php                        # Enhanced message model
└── Providers/
    └── AppServiceProvider.php             # No changes needed

resources/views/vendor/backpack/
├── base/
│   ├── dashboard.blade.php                # Enhanced dashboard with messaging widgets
│   └── inc/
│       └── sidebar_content.blade.php      # Added messaging menu items
└── crud/messages/
    ├── conversations.blade.php            # Conversations list view
    └── conversation.blade.php             # Individual conversation view

routes/
└── backpack/
    └── custom.php                         # Added messaging routes

database/migrations/
└── 2024_12_19_000000_add_is_read_to_messages_table.php
```

### 3. **Routes**
The following routes are automatically available:

- `GET /admin/message-conversations` - View all conversations
- `GET /admin/message-conversation/{userId}` - View conversation with specific user
- `POST /admin/message-send` - Send message to user
- `GET /admin/message-messages/{userId}` - Get messages for a conversation
- `GET /admin/message` - View all messages (CRUD list)

## Usage

### **Accessing the Messaging System**

1. **Login as Support Service/Super Admin** (user_id = 1)
2. **Navigate to Admin Panel** → `/admin`
3. **Click on "Messaging"** in the left sidebar
4. **Choose from two options:**
   - **Conversations** - View all user conversations
   - **All Messages** - View all messages in the system

### **Managing Conversations**

#### **View All Conversations**
- Navigate to **Messaging** → **Conversations**
- See list of all users with active conversations
- View last message, timestamp, and unread count
- Click **"Open Chat"** to start chatting with a user

#### **Individual Conversation**
- Click on any conversation to open the chat interface
- View complete message history
- Send new messages using the input field
- Messages are sent in real-time
- Auto-refresh every 10 seconds

### **Sending Messages**

1. **Open a conversation** with a user
2. **Type your message** in the input field
3. **Click "Send"** or press Enter
4. **Message appears immediately** in the chat
5. **User receives the message** in their mobile app

### **Dashboard Widgets**

When logged in as Support Service/Super Admin, the dashboard shows:

- **Support Messages** card - Quick access to messaging
- **Quick Actions** card - Send messages to users
- **User Management** card - Manage user accounts
- **Recent Conversations** widget - Latest 5 conversations

## Security Features

### **Access Control**
```php
// Only user_id = 1 can access messaging
$this->middleware(function ($request, $next) {
    if (auth()->id() != 1) {
        abort(403, 'Access denied. Only support staff can access messaging.');
    }
    return $next($request);
});
```

### **CSRF Protection**
All messaging forms include CSRF tokens for security.

### **Input Validation**
```php
$request->validate([
    'receiver_id' => 'required|exists:users,id',
    'message' => 'required|string|max:1000'
]);
```

## API Endpoints

### **For Admin Panel (Internal)**
- `GET /admin/message-conversations` - Get all conversations
- `GET /admin/message-conversation/{userId}` - Get conversation with user
- `POST /admin/message-send` - Send message to user
- `GET /admin/message-messages/{userId}` - Get messages for user

### **For Mobile Apps (External)**
- `GET /api/conversations` - Get user conversations
- `GET /api/messages/{userId}` - Get messages with user
- `POST /api/send-message` - Send message

## Database Schema

### **Messages Table**
```sql
CREATE TABLE messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_id BIGINT UNSIGNED NOT NULL,
    receiver_id BIGINT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### **New Column Added**
- `is_read` - Boolean flag to track read/unread messages

## Mobile App Integration

### **Sender App (PiqDrop)**
- Users can send messages to droppers and support
- Users can receive messages from droppers and support
- Support conversation is automatically available

### **Dropper/Rider App (PiqRider)**
- Riders can send messages to senders and support
- Riders can receive messages from senders and support
- Support conversation is automatically available

### **Support Service**
- Support staff can send messages to any user
- Support staff can view all conversations
- Support staff cannot receive replies (one-way communication)

## Troubleshooting

### **Common Issues**

1. **"Access denied" error**
   - Ensure you're logged in as user_id = 1
   - Check if the middleware is properly configured

2. **Messages not appearing**
   - Check browser console for JavaScript errors
   - Verify AJAX requests are working
   - Check database connection

3. **Conversations not loading**
   - Ensure the messages table exists
   - Check if there are messages in the database
   - Verify user relationships

### **Debug Mode**
Enable debug mode in `.env`:
```env
APP_DEBUG=true
```

### **Logs**
Check Laravel logs in `storage/logs/laravel.log` for any errors.

## Customization

### **Styling**
- Modify CSS in the view files to match your brand
- Update color schemes in the conversation views
- Customize dashboard widget appearance

### **Functionality**
- Add file/image sharing capabilities
- Implement message notifications
- Add message search functionality
- Create message templates for common responses

### **Permissions**
- Modify the middleware to allow other user roles
- Add role-based access control
- Implement department-based messaging

## Support

For technical support or questions about the messaging system:

- **Developer**: Ashraful Islam
- **Email**: [Your Email]
- **Documentation**: This README file

## Version History

- **v1.0.0** - Initial release with basic messaging functionality
- **v1.1.0** - Added unread message tracking
- **v1.2.0** - Enhanced dashboard integration
- **v1.3.0** - Added real-time updates and security features

---

**Note**: This messaging system is designed specifically for PiqDrop's support operations. Ensure all security measures are in place before deploying to production.    