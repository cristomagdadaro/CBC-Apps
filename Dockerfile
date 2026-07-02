# Stage 1: Install composer dependencies
FROM composer:2.7 AS composer-build

WORKDIR /var/www/html

# Copy composer.json and composer.lock
COPY composer.json composer.lock ./

# Install dependencies without scripts and autoloader first for caching
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

# Copy the rest of the application
COPY . .

# Provide dummy env vars so package:discover doesn't fail due to missing keys
ENV REVERB_APP_ID=dummy \
    REVERB_APP_KEY=dummy \
    REVERB_APP_SECRET=dummy \
    PUSHER_APP_ID=dummy \
    PUSHER_APP_KEY=dummy \
    PUSHER_APP_SECRET=dummy

# Dump autoloader and run scripts
RUN composer dump-autoload --optimize --no-dev

# Stage 2: Build frontend assets
FROM node:20 AS node-build

WORKDIR /var/www/html

# Copy package.json and package-lock.json
COPY package*.json ./

# Install npm dependencies
RUN npm ci

# Copy the rest of the application
COPY . .

# Copy vendor directory from composer-build to resolve Ziggy and other vendor assets
COPY --from=composer-build /var/www/html/vendor /var/www/html/vendor

# Build frontend assets
RUN npm run build

# Stage 3: Final PHP-FPM image
FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    supervisor \
    default-mysql-client

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip xml

# Copy custom PHP configuration
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Set working directory
WORKDIR /var/www/html

# Copy from composer stage
COPY --from=composer-build /var/www/html /var/www/html

# Copy from node stage
COPY --from=node-build /var/www/html/public/build /var/www/html/public/build

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Start PHP-FPM server
CMD ["php-fpm"]
