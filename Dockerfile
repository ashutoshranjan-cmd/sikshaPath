# =========================================
# Frontend build (Laravel Mix / Webpack)
# =========================================
FROM node:20-alpine AS frontend

WORKDIR /app

# Install JS deps first (better layer caching)
COPY package*.json ./
RUN npm ci

# Copy the rest of the project and build production assets
COPY . .
RUN npm run production


# =========================================
# Backend (Laravel + Apache + PHP extensions)
# =========================================
FROM php:8.2-apache

# System deps + PHP extensions required by your composer.json
RUN apt-get update && apt-get install -y \
    git unzip zip libzip-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd mbstring \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copy Laravel source
COPY . .

# Install Composer deps (no-dev for production)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy built public assets from Mix build stage (overwrites /public)
COPY --from=frontend /app/public /var/www/html/public

# Apache document root -> /public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
 && sed -i 's|/var/www/|/var/www/html/public|g' /etc/apache2/apache2.conf

# Laravel permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
