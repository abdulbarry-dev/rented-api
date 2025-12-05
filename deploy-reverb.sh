#!/bin/bash

# Laravel Reverb Deployment Script for VPS
# This script deploys and configures Laravel Reverb with Docker

set -e

echo "==================================="
echo "Laravel Reverb Deployment Script"
echo "==================================="
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ Error: .env file not found"
    echo "Please copy .env.example to .env and configure it first"
    exit 1
fi

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Error: Docker is not running"
    exit 1
fi

# Generate Reverb credentials if not set
if ! grep -q "REVERB_APP_ID" .env || [ -z "$(grep REVERB_APP_ID .env | cut -d '=' -f2)" ]; then
    echo "📝 Generating Reverb credentials..."
    
    # Generate random credentials
    APP_ID=$(openssl rand -hex 16)
    APP_KEY=$(openssl rand -hex 32)
    APP_SECRET=$(openssl rand -hex 32)
    
    # Update .env file
    if grep -q "REVERB_APP_ID" .env; then
        sed -i "s/REVERB_APP_ID=.*/REVERB_APP_ID=$APP_ID/" .env
        sed -i "s/REVERB_APP_KEY=.*/REVERB_APP_KEY=$APP_KEY/" .env
        sed -i "s/REVERB_APP_SECRET=.*/REVERB_APP_SECRET=$APP_SECRET/" .env
    else
        echo "" >> .env
        echo "# Reverb Configuration" >> .env
        echo "REVERB_APP_ID=$APP_ID" >> .env
        echo "REVERB_APP_KEY=$APP_KEY" >> .env
        echo "REVERB_APP_SECRET=$APP_SECRET" >> .env
    fi
    
    echo "✅ Reverb credentials generated"
fi

# Update environment variables for VPS
echo "🔧 Configuring Reverb for VPS..."

# Get server IP
SERVER_IP=$(curl -s ifconfig.me || echo "localhost")
echo "Server IP: $SERVER_IP"

# Update REVERB_HOST if not already set correctly
if ! grep -q "REVERB_HOST=.*$SERVER_IP" .env; then
    if grep -q "REVERB_HOST=" .env; then
        sed -i "s/REVERB_HOST=.*/REVERB_HOST=$SERVER_IP/" .env
    else
        echo "REVERB_HOST=$SERVER_IP" >> .env
    fi
fi

# Ensure other Reverb settings
grep -q "REVERB_PORT=" .env || echo "REVERB_PORT=8080" >> .env
grep -q "REVERB_SCHEME=" .env || echo "REVERB_SCHEME=http" >> .env
grep -q "BROADCAST_CONNECTION=" .env || echo "BROADCAST_CONNECTION=reverb" >> .env
grep -q "REVERB_SCALING_ENABLED=" .env || echo "REVERB_SCALING_ENABLED=true" >> .env

echo "✅ Reverb configuration updated"

# Stop existing Reverb container if running
echo "🛑 Stopping existing Reverb container..."
docker compose stop reverb 2>/dev/null || true
docker compose rm -f reverb 2>/dev/null || true

# Build and start Reverb
echo "🚀 Starting Reverb WebSocket server..."
docker compose up -d reverb

# Wait for Reverb to be healthy
echo "⏳ Waiting for Reverb to be ready..."
sleep 5

# Check if Reverb is running
if docker compose ps reverb | grep -q "Up"; then
    echo "✅ Reverb is running successfully!"
    echo ""
    echo "==================================="
    echo "Reverb WebSocket Server Info"
    echo "==================================="
    echo "Host: $SERVER_IP"
    echo "Port: 8080"
    echo "WebSocket URL: ws://$SERVER_IP:8080"
    echo ""
    echo "📋 Configuration for your frontend:"
    echo "VITE_REVERB_APP_KEY=$(grep REVERB_APP_KEY .env | cut -d '=' -f2)"
    echo "VITE_REVERB_HOST=$SERVER_IP"
    echo "VITE_REVERB_PORT=8080"
    echo "VITE_REVERB_SCHEME=http"
    echo ""
    echo "🔍 Check logs: docker compose logs -f reverb"
    echo "🔄 Restart: docker compose restart reverb"
    echo "🛑 Stop: docker compose stop reverb"
else
    echo "❌ Failed to start Reverb"
    echo "Check logs: docker compose logs reverb"
    exit 1
fi
