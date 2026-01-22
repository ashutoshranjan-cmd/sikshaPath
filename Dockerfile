# =========================================
# 1) Frontend build (Laravel Mix / Webpack)
# =========================================
FROM node:20-alpine AS frontend
WORKDIR /app

# Install JS deps first (cache-friendly)
COPY package*.json ./
RUN npm ci

# Copy only what Mix needs (faster & avoids overriding node_modules)
COPY resources/ ./resources/
COPY public/ ./public/
COPY webpack.mix.js ./
# If your mix uses these, keep them:
COPY vite.config.js* ./
COPY tailwind.config.* postcss.config.* babel.config.* . 2>/dev/null || true

# Build production assets (creates public/css + public/js)
RUN npm run production


# =========================================
# 2) Composer deps (build vendor/)
# =========================================
FROM composer:2 AS vendor
WORKDIR /app

# Copy composer files + local path packages FIRST
COPY composer.json composer.lock ./
COPY packages/ ./packages/

# Install vendor
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader


# =========================================
# 3) Runtime (PHP 8.2 + Apache)
# =========================================
FROM php:8.2-apache

# System deps + PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip zip libzip-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd mbstring \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copy application code
COPY . .

# Bring in vendor + built assets
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public ./public

# Apache document root -> /public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
 && sed -i 's|/var/www/|/var/www/html/public|g' /etc/apache2/apache2.conf

# Laravel permissions
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
