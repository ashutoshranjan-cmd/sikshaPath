# 1) Build frontend (Vite)
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# 2) Backend (Laravel + Apache)
FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    unzip zip git libzip-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd mbstring \
    && a2enmod rewrite

WORKDIR /var/www/html
COPY . .

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy built Vite assets into public/build
COPY --from=frontend /app/public/build /var/www/html/public/build

# Apache docroot -> public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
 && sed -i 's|/var/www/|/var/www/html/public|g' /etc/apache2/apache2.conf

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
