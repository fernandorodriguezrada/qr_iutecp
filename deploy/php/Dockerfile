FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    tzdata

# Configure and install PHP extensions required by the QR system
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        gd \
        zip \
        opcache

# Set timezone
ENV TZ=America/Caracas
RUN cp /usr/share/zoneinfo/America/Caracas /etc/localtime && echo "America/Caracas" > /etc/timezone

# Production PHP settings
RUN { \
    echo 'date.timezone = "America/Caracas"'; \
    echo 'upload_max_filesize = 20M'; \
    echo 'post_max_size = 25M'; \
    echo 'memory_limit = 256M'; \
    echo 'max_execution_time = 60'; \
    echo 'opcache.enable = 1'; \
    echo 'opcache.memory_consumption = 128'; \
    echo 'opcache.interned_strings_buffer = 8'; \
    echo 'opcache.max_accelerated_files = 4000'; \
    echo 'opcache.revalidate_freq = 2'; \
    echo 'opcache.fast_shutdown = 1'; \
    echo 'session.cookie_httponly = 1'; \
    echo 'session.use_only_cookies = 1'; \
    echo 'session.cookie_samesite = "Lax"'; \
} > /usr/local/etc/php/conf.d/production.ini

WORKDIR /var/www/html
