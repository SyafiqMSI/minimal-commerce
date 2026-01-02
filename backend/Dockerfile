FROM php:8.4-cli

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    curl \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip pcntl gd \
    && rm -rf /var/lib/apt/lists/*

RUN echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/memory-limit.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install --optimize-autoloader --no-dev --no-scripts

COPY . .

RUN composer dump-autoload --optimize

RUN mkdir -p /var/www/storage/logs \
    /var/www/storage/framework/cache \
    /var/www/storage/framework/sessions \
    /var/www/storage/framework/views \
    /var/www/storage/app/public \
    && chown -R www-data:www-data /var/www/storage \
    && chown -R www-data:www-data /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

EXPOSE 2465

CMD php artisan config:cache && \
    php artisan route:cache && \
    php artisan storage:link --force || true && \
    php artisan serve --host=0.0.0.0 --port=2465

