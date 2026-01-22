# =========================================================
# 1) Frontend build (Laravel Mix / Webpack)
# =========================================================
FROM node:20-alpine AS frontend
WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci

COPY resources/ ./resources/
COPY public/ ./public/
COPY webpack.mix.js ./
# If your project has them, uncomment:
# COPY postcss.config.js* ./
# COPY tailwind.config.js* ./
# COPY babel.config.js* ./
# COPY .babelrc* ./

RUN npm run production


# =========================================================
# 2) PHP base with required extensions
# =========================================================
FROM php:8.2-apache AS phpbase

RUN apt-get update && apt-get install -y \
    git unzip zip libzip-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd mbstring \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


# =========================================================
# 3) Vendor stage (composer install must see autoload files)
# =========================================================
FROM phpbase AS vendor
WORKDIR /var/www/html

COPY composer.json composer.lock* ./

# IMPORTANT: copy the autoloaded helper files so composer doesn't crash
COPY app/ ./app/

# Remove invalid local path repository entry (if present)
RUN php -r '$f="composer.json"; $j=json_decode(file_get_contents($f), true); if(isset($j["repositories"]) && is_array($j["repositories"])) { $j["repositories"]=array_values(array_filter($j["repositories"], function($r){ return !(isset($r["type"],$r["url"]) && $r["type"]==="path" && $r["url"]==="packages/laravel-wizard-installer"); })); } file_put_contents($f, json_encode($j, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));'

RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader


# =========================================================
# 4) Runtime image
# =========================================================
FROM phpbase AS runtime
WORKDIR /var/www/html

COPY . .
COPY --from=vendor /var/www/html/vendor /var/www/html/vendor
COPY --from=frontend /app/public /var/www/html/public

# Apache docroot -> /public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
 && sed -i 's|/var/www/|/var/www/html/public|g' /etc/apache2/apache2.conf

# Ensure Laravel writable dirs exist (fix sessions/cache errors)
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

EXPOSE 80
