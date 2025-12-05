# Laravel Reverb WebSocket Server - Docker Deployment

This guide explains how to deploy and use Laravel Reverb WebSocket server with Docker on your VPS.

## What is Laravel Reverb?

Laravel Reverb is a blazing-fast, scalable WebSocket server for Laravel applications. It enables real-time features like:
- Live notifications
- Real-time chat
- Live updates
- Presence channels (who's online)

## Quick Start

### 1. Automated Deployment (Recommended)

Run the deployment script on your VPS:

```bash
./deploy-reverb.sh
```

This script will:
- Generate secure Reverb credentials
- Configure Reverb for your VPS IP
- Start the Reverb Docker container
- Display connection information

### 2. Manual Setup

If you prefer manual setup:

#### Step 1: Update .env

Add these variables to your `.env` file:

```env
# Broadcasting
BROADCAST_CONNECTION=reverb

# Reverb Server Configuration
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=your-vps-ip
REVERB_PORT=8080
REVERB_SCHEME=http

# Internal Reverb Server Settings
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080
REVERB_SCALING_ENABLED=true
REVERB_SCALING_CHANNEL=reverb
```

**Generate credentials:**
```bash
# Generate random secure credentials
openssl rand -hex 16  # For APP_ID
openssl rand -hex 32  # For APP_KEY
openssl rand -hex 32  # For APP_SECRET
```

#### Step 2: Start Reverb

```bash
docker compose up -d reverb
```

#### Step 3: Verify It's Running

```bash
docker compose ps reverb
docker compose logs -f reverb
```

## Docker Compose Configuration

The Reverb service is configured in `docker-compose.yml`:

```yaml
reverb:
  build:
    context: .
    dockerfile: Dockerfile
  container_name: rented_api_reverb
  restart: unless-stopped
  command: php artisan reverb:start --host=0.0.0.0 --port=8080
  ports:
    - "8080:8080"
  environment:
    - REVERB_SERVER_HOST=0.0.0.0
    - REVERB_SERVER_PORT=8080
    - REVERB_SCALING_ENABLED=true
  depends_on:
    - redis
    - postgres
```

## Frontend Configuration

Configure your Flutter/React/Vue app to connect to Reverb:

### Flutter (laravel_echo)

```dart
import 'package:laravel_echo/laravel_echo.dart';

Echo echo = Echo({
  'broadcaster': 'reverb',
  'key': 'your-app-key',
  'wsHost': 'your-vps-ip',
  'wsPort': 8080,
  'wssPort': 8080,
  'forceTLS': false,
  'encrypted': false,
  'disableStats': true,
  'enabledTransports': ['ws', 'wss'],
});
```

### JavaScript (Laravel Echo)

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: 'your-app-key',
    wsHost: 'your-vps-ip',
    wsPort: 8080,
    wssPort: 8080,
    forceTLS: false,
    encrypted: false,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
});
```

## Usage Examples

### 1. Broadcasting Events

Create an event in Laravel:

```bash
php artisan make:event MessageSent
```

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $message,
        public int $userId
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('chat');
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
```

Broadcast the event:

```php
use App\Events\MessageSent;

broadcast(new MessageSent('Hello World!', auth()->id()));
```

### 2. Listening on Frontend (Flutter)

```dart
// Listen to public channel
echo.channel('chat').listen('message.sent', (e) {
  print('New message: ${e['message']}');
});

// Listen to private channel (requires authentication)
echo.private('user.${userId}').listen('notification', (e) {
  print('New notification: ${e['data']}');
});
```

### 3. Private Channels (Authentication Required)

Define channel authorization in `routes/channels.php`:

```php
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
```

Frontend must authenticate:

```dart
echo.connector.pusher.config.auth = {
  'headers': {
    'Authorization': 'Bearer $token',
  },
};
```

## Monitoring & Troubleshooting

### Check Reverb Status

```bash
# View logs
docker compose logs -f reverb

# Check if container is running
docker compose ps reverb

# Restart Reverb
docker compose restart reverb

# Stop Reverb
docker compose stop reverb
```

### Test WebSocket Connection

```bash
# Install wscat (WebSocket client)
npm install -g wscat

# Connect to Reverb
wscat -c ws://your-vps-ip:8080/app/your-app-key
```

### Common Issues

**Issue**: Connection refused
- Check if port 8080 is open in your firewall
- Verify Reverb container is running: `docker compose ps reverb`

**Issue**: Authentication failed
- Verify `REVERB_APP_KEY` matches in backend and frontend
- Check Sanctum token is valid

**Issue**: Messages not broadcasting
- Ensure `BROADCAST_CONNECTION=reverb` in `.env`
- Check Redis is running: `docker compose ps redis`
- Verify event implements `ShouldBroadcast`

## Scaling Reverb

Reverb supports horizontal scaling using Redis:

```env
REVERB_SCALING_ENABLED=true
REVERB_SCALING_CHANNEL=reverb
```

This allows multiple Reverb instances to communicate and share connections.

## Security Considerations

### Production Deployment

1. **Use HTTPS/WSS**:
   ```env
   REVERB_SCHEME=https
   ```

2. **Setup SSL Certificate**:
   - Use Let's Encrypt with Certbot
   - Configure Nginx reverse proxy for WSS

3. **Firewall Configuration**:
   ```bash
   # Allow WebSocket port
   sudo ufw allow 8080/tcp
   ```

4. **Rate Limiting**:
   Configure in `config/reverb.php`:
   ```php
   'max_request_size' => env('REVERB_MAX_REQUEST_SIZE', 10_000),
   ```

## Performance Tuning

### Optimize Redis Connection

```env
REDIS_CLIENT=phpredis  # Faster than predis
REDIS_TIMEOUT=60
```

### Increase Connection Limits

Modify Docker container resources:

```yaml
reverb:
  deploy:
    resources:
      limits:
        cpus: '2'
        memory: 2G
```

## Useful Commands

```bash
# View real-time stats
docker compose exec reverb php artisan reverb:stats

# Clear all connections
docker compose restart reverb

# Update Reverb configuration
docker compose up -d --force-recreate reverb

# View application logs with Reverb events
docker compose logs -f app reverb
```

## Integration with Your Rented Marketplace

### Real-Time Features You Can Implement

1. **Live Notifications**:
   - New rental requests
   - Offer updates
   - Payment confirmations

2. **Chat System**:
   - Real-time messaging between users
   - Typing indicators
   - Read receipts

3. **Product Updates**:
   - Availability changes
   - Price updates
   - New offers

4. **Presence Channels**:
   - Show online users
   - Active product viewers

## Support & Documentation

- [Laravel Reverb Docs](https://laravel.com/docs/reverb)
- [Laravel Broadcasting](https://laravel.com/docs/broadcasting)
- [Laravel Echo](https://github.com/laravel/echo)

## License

This configuration is part of the Rented Marketplace API project.
