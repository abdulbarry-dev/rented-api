# Docker Deployment Guide

## Issue: 403 Forbidden on Static Files

### Problem
When using Laravel Octane with Swoole, static files (like product images) are not served automatically. Swoole is an application server, not a web server, so it doesn't handle static file serving by default.

### Solution
Use Nginx as a reverse proxy in front of Octane to:
- Serve static files directly (images, CSS, JS)
- Proxy dynamic requests to Octane
- Handle proper caching headers

## Architecture

```
Browser → Nginx (Port 8000) → Octane (Port 8000 internal)
           ↓
        Static Files (served directly)
```

## Deployment Steps

### 1. Update Your VPS

SSH into your VPS and update the repository:

```bash
cd /path/to/rented-api
git pull origin main
```

### 2. Rebuild Docker Containers

```bash
# Stop existing containers
sudo docker compose down

# Remove old images (optional, ensures clean build)
sudo docker compose build --no-cache

# Start with new configuration
sudo docker compose up -d
```

### 3. Verify Services

```bash
# Check all containers are running
sudo docker compose ps

# Check nginx logs
sudo docker compose logs nginx

# Check app logs
sudo docker compose logs app
```

### 4. Test Static File Access

```bash
# Test API endpoint
curl http://YOUR_VPS_IP:8000/api/v1/

# Test a product with images
curl http://YOUR_VPS_IP:8000/api/v1/products/1

# Test direct image access (replace with actual image URL)
curl -I http://YOUR_VPS_IP:8000/storage/products/images/FILENAME.jpg
```

Expected response for image:
```
HTTP/1.1 200 OK
Content-Type: image/jpeg
```

## Configuration Files

### nginx.conf
- Serves static files directly from `/storage/` and `/public/`
- Proxies API requests to Octane on port 8000
- Adds proper caching headers for static assets

### docker-compose.yml
- **nginx**: Exposes port 8000 to host, serves static files
- **app**: Runs Octane internally, not exposed to host
- Both services share volumes for storage and public directories

## Important Notes

### File Permissions
The Dockerfile now sets proper permissions:
```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
chmod -R 775 public/
```

### Storage Link
The entrypoint script automatically creates the storage symlink:
```bash
php artisan storage:link
```

### For Production

Update `.env.docker` with your production values:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://YOUR_VPS_IP:8000

# Or with your domain
APP_URL=https://api.yourdomain.com
```

### Using a Custom Domain

If you want to use a domain instead of IP:

1. Point your domain DNS to your VPS IP
2. Update `APP_URL` in `.env.docker`
3. Optionally add SSL with Let's Encrypt:

```yaml
# In docker-compose.yml, add to nginx service
ports:
  - "80:80"
  - "443:443"
volumes:
  - ./nginx.conf:/etc/nginx/conf.d/default.conf
  - ./ssl:/etc/nginx/ssl  # Add SSL certificates here
```

## Troubleshooting

### 403 Forbidden on Images

Check:
1. Storage link exists: `docker compose exec app ls -la public/storage`
2. File permissions: `docker compose exec app ls -la storage/app/public/products/images/`
3. Nginx logs: `docker compose logs nginx`

### Images Not Found (404)

Check:
1. Files exist: `docker compose exec app ls storage/app/public/products/images/`
2. Volume mounts in docker-compose.yml are correct
3. Storage link points to correct directory

### Container Won't Start

Check logs:
```bash
docker compose logs app
docker compose logs nginx
```

Common issues:
- Port 8000 already in use: Change port mapping in docker-compose.yml
- Permission denied: Run with sudo or fix file ownership

## Quick Commands

```bash
# Rebuild everything
sudo docker compose down && sudo docker compose build --no-cache && sudo docker compose up -d

# View logs
sudo docker compose logs -f

# Restart a service
sudo docker compose restart nginx
sudo docker compose restart app

# Execute commands in container
sudo docker compose exec app php artisan storage:link
sudo docker compose exec app php artisan migrate

# Check storage link
sudo docker compose exec app ls -la public/storage
```

## Security Notes

1. **In production**, remove the public port exposure from docker-compose.yml and use a proper reverse proxy (nginx on host)
2. **Enable HTTPS** with Let's Encrypt
3. **Set proper firewall rules** to allow only port 80/443
4. **Use strong database passwords**
5. **Set APP_DEBUG=false** in production

## Performance Tips

1. Nginx caches static files for 1 year (expires 1y)
2. Octane keeps the application in memory for fast responses
3. Redis is used for cache and sessions
4. Consider using a CDN for static assets in production
