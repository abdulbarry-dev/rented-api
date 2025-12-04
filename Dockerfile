# Use official PHP 8.4 FPM image as base
FROM php:8.4-cli

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libpq-dev \
    libpq5 \
    postgresql-client \
    libbrotli-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure and install GD with JPEG support
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    sockets

# Install Swoole extension for Laravel Octane
RUN pecl install swoole \
    && docker-php-ext-enable swoole

# Install Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Copy environment file
COPY .env.docker .env

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/public

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy and set up entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose port for Octane
EXPOSE 8000

# Use entrypoint script to initialize the application
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
