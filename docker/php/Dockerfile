FROM composer:2 as composer
FROM php:7.4-fpm

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APCU_VERSION 5.1.18

# Add Composer to PHP container
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

# Install recommended extensions for Symfony
RUN apt-get update && apt-get install -y \
        libicu-dev \
    && docker-php-ext-install \
        intl \
        opcache \
    && docker-php-ext-enable \
        intl \
        opcache

# MongoDB extension for PHP 7
# The libssl-dev is necessary to avoid an error while mongodb extension install
RUN apt-get update && apt-get install -y libssl-dev
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# Install GD
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

RUN docker-php-ext-install pdo_mysql

# Fix permissions
RUN usermod -u 1000 www-data

# PHPUnit
RUN curl "https://phar.phpunit.de/phpunit.phar" -L -o "phpunit.phar"
RUN chmod +x phpunit.phar
RUN mv phpunit.phar /usr/local/bin/phpunit

# Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug
