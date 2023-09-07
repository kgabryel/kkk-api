FROM php:8.1-fpm
RUN apt-get update && apt-get install -y libpq-dev libpng-dev zlib1g-dev git libzip-dev libjpeg-dev libmagickwand-dev \
    && pecl install imagick \
    && docker-php-ext-install pdo pdo_pgsql gd zip pcntl \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-enable imagick
COPY xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
COPY error_reporting.ini /usr/local/etc/php/conf.d/error_reporting.ini