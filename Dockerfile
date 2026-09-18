# ===============================
# 1) Backend dependencies (Composer)
# ===============================
FROM dunglas/frankenphp:php8.4-alpine AS backend

WORKDIR /app

# Install system dependencies + PHP extension build deps
RUN apk add --no-cache \
    git \
    unzip \
    zip \
    curl \
    icu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libpq-dev \
    $PHPIZE_DEPS

RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg

# Install required PHP extensions
RUN docker-php-ext-install \
    intl \
    zip \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    bcmath \
    opcache \
    gd \
    exif

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Copy composer files only (cache friendly)
COPY composer.json composer.lock ./

# Install PHP deps
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-scripts \
    --optimize-autoloader


# ===============================
# 2) Frontend build (Vite)
# ===============================
FROM node:22-alpine AS frontend

WORKDIR /app

# Copy app source
COPY . .

# Copy vendor so Tailwind/Livewire/Flux can scan it
COPY --from=backend /app/vendor /app/vendor

RUN npm install
RUN npm run build


# ===============================
# 3) Runtime image (FrankenPHP)
# ===============================
FROM dunglas/frankenphp:php8.4-alpine

WORKDIR /app

ENV SERVER_NAME=:80

# Install runtime libs + PHP extensions
RUN apk add --no-cache \
    icu \
    libzip \
    unzip \
    zip \
    curl \
    oniguruma

RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libpq-dev \
    $PHPIZE_DEPS

RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg

RUN docker-php-ext-install \
    intl \
    zip \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    bcmath \
    opcache \
    gd \
    exif

# Production PHP config
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory-limit.ini \
    && echo "max_execution_time=300" > /usr/local/etc/php/conf.d/timeout.ini \
    && echo "upload_max_filesize=100M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=100M" >> /usr/local/etc/php/conf.d/uploads.ini

# Copy app source
COPY . .

# Copy built dependencies/assets
COPY --from=backend /app/vendor /app/vendor
COPY --from=backend /usr/local/bin/composer /usr/local/bin/composer
COPY --from=frontend /app/public/build /app/public/build

# Ensure Laravel dirs exist
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache

# Optimize Laravel
RUN composer run-script post-autoload-dump

# Storage link
RUN php artisan storage:link || true

# Permissions
RUN chown -R www-data:www-data \
    /app/storage \
    /app/bootstrap/cache \
    /app/public/storage

EXPOSE 80
