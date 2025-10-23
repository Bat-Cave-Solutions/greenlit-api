# syntax=docker/dockerfile:1.7
FROM php:8.3-fpm-alpine

# System deps
RUN apk add --no-cache git bash curl libpq-dev oniguruma-dev autoconf make g++ icu-dev zlib-dev

# PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring bcmath intl pcntl
RUN pecl install redis && docker-php-ext-enable redis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# App setup
WORKDIR /var/www

# Optional: speed up composer via ini tweaks
RUN { \
    echo 'memory_limit=-1'; \
    echo 'opcache.enable=1'; \
    echo 'opcache.validate_timestamps=0'; \
  } > /usr/local/etc/php/conf.d/app.ini

# Copy only composer files to leverage Docker cache
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts || true

# Copy the rest (if building after project exists)
COPY . .

# Final composer to ensure vendors are present in dev
RUN composer install --no-interaction

CMD ["php-fpm"]