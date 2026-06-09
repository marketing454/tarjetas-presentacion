FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev \
    zip unzip libzip-dev libgd-dev libmagickwand-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring gd zip exif pcntl \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN echo "upload_max_filesize = 10M" > /usr/local/etc/php/conf.d/custom.ini \
    && echo "post_max_size = 12M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html
