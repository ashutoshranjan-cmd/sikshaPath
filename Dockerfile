# =========================================================
# 1) Frontend build (Laravel Mix / Webpack)
# =========================================================
FROM node:20-alpine AS frontend
WORKDIR /app

# Install JS deps first (cache-friendly)
COPY package.json package-lock.json* ./
RUN npm ci

# Copy only what Mix needs to build
COPY resources/ ./resources/
COPY public/ ./public/
COPY webpack.mix.js ./
# If you have these files, they help Mix (safe even if absent? no — keep only if exists)
COPY vite.config.js* ./
COPY postcss.config.js* ./
COPY tailwind.config.js* ./
COPY babel.config.js* ./
COPY .babelrc* ./
COPY .env.example* ./

# Your package.json uses laravel-mix, so production build is:
RUN npm run production


# =========================================================
# 2) Composer vendor install (no-dev)
#    NOTE: your composer.json has a path repo "packages/laravel-wizard-installer"
#    but your project does NOT have /packages -> so we must remove it OR add it.
#    We remove it inside image to prevent composer failing.
# =========================================================
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock* ./

# Remove the invalid "path" repository so composer can install successfully.
# (Without this, composer tries to load packages/laravel-wizard-installer and fails.)
RUN php -r '
$f="composer.json";
$j=json_decode(file_get_contents($f), true);
if(isset($j["repositories"]) && is_array($j["repositories"])) {
$j["repositories"]=array_values(array_filter($j["repositories"], function($r){
return !isset($r["type"], $r["url"]) || !($r["type"]==="path" && $r["url"]==="packages/laravel-wizard-installer");
}));
}
file_put_contents($f, json_encode($j, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
'

RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader


# =========================================================
# 3) Runtime (PHP 8.2 + Apache)
# =========================================================
FROM php:8.2-apache

# System deps + PHP extensions used by typical Laravel apps
# Add libonig-dev to fix: "Package 'oniguruma' not found" when building mbstring
RUN apt-get update && apt-get install -y \
    git unzip zip libzip-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd mbstring \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copy app source
COPY . .

# Copy vendor from composer stage
COPY --from=vendor /app/vendor /var/www/html/vendor

# Copy compiled assets from frontend stage
COPY --from=frontend /app/public /var/www/html/public

# Apache docroot -> /public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/|/var/www/html/public|g' /etc/apache2/apache2.conf

# Ensure Laravel runtime folders exist + writable (fixes file_put_contents storage/framework/sessions...)
RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Optional: if you use "public/storage" symlink
# RUN php artisan storage:link || true

EXPOSE 80
