# Laravel Reverb WebSocket Setup Guide

## Overview
This application uses Laravel Reverb for real-time WebSocket communication for messaging and notifications.

## Configuration

### 1. Environment Variables
Add these to your `.env` file:

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=rented-app
REVERB_APP_KEY=rented-app-key
REVERB_APP_SECRET=rented-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

For production:
```env
REVERB_HOST=your-domain.com
REVERB_PORT=443
REVERB_SCHEME=https
```

### 2. Start Reverb Server
```bash
php artisan reverb:start
```

Or run in background:
```bash
php artisan reverb:start --host=0.0.0.0 --port=8080
```

### 3. Broadcasting Configuration
The default broadcaster is set to `reverb` in `config/broadcasting.php`.

## Events

### Message Events
- **MessageSent**: Broadcasts when a new message is sent
- **MessageRead**: Broadcasts when messages are marked as read
- **TypingIndicator**: Broadcasts typing status

### Rental Events
- **RentalCreated**: Broadcasts when a new rental is created
- **RentalStatusChanged**: Broadcasts when rental status changes

## Channels

### Private Channels
- `user.{userId}` - User-specific notifications

### Presence Channels
- `conversation.{conversationId}` - Conversation messages and typing indicators

## API Endpoints

### Messages
- `POST /api/v1/messages` - Send a message (broadcasts MessageSent)
- `POST /api/v1/conversations/{id}/mark-read` - Mark messages as read (broadcasts MessageRead)
- `POST /api/v1/conversations/{id}/typing` - Send typing indicator (broadcasts TypingIndicator)

### Rentals
- `POST /api/v1/rentals` - Create rental (broadcasts RentalCreated)
- `PUT /api/v1/rentals/{id}` - Update rental status (broadcasts RentalStatusChanged)

## Flutter Integration

The Flutter app uses the `WebSocketService` to connect to Reverb. See `lib/services/websocket_service.dart` for implementation.

### Connection
```dart
final wsService = WebSocketService();
await wsService.connect();
```

### Subscribe to Conversation
```dart
await wsService.subscribeToConversation(conversationId);
```

### Listen to Events
```dart
wsService.onMessage = (message) {
  // Handle new message
};

wsService.onRentalNotification = (notification) {
  // Handle rental notification
};

wsService.onTyping = (typingData) {
  // Handle typing indicator
};
```

## Testing

1. Start Reverb server: `php artisan reverb:start`
2. Send a message via API
3. Check WebSocket connection in Flutter app
4. Verify events are received in real-time

