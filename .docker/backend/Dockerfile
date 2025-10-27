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
    echo 'opcache.validate_timestamps=1'; \
  } > /usr/local/etc/php/conf.d/app.ini

# Copy only composer files to leverage Docker cache
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts || true

# Copy the rest (if building after project exists)
COPY . .

# Ensure Laravel writable directories exist and are owned by the www-data user
# This makes the container behave correctly when code is bind-mounted from the host
RUN mkdir -p /var/www/storage /var/www/bootstrap/cache \
 && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
 && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Final composer to ensure vendors are present in dev
RUN composer install --no-interaction

# Copy and enable entrypoint which ensures runtime permissions are correct
COPY .docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]