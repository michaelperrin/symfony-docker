FROM php:7.0-fpm
MAINTAINER Michaël Perrin <contact@michaelperrin.fr>

# Necessary packages for Composer
RUN apt-get update \
    && apt-get install -y \
        git \
        unzip

# Install recommended extensions for Symfony
RUN apt-get update && apt-get install -y \
        libicu-dev \
    && docker-php-ext-install \
        intl \
        opcache \
    && docker-php-ext-enable \
        intl \
        opcache

# Install GD
RUN apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng12-dev \
    && docker-php-ext-install gd \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/

RUN docker-php-ext-install pdo_mysql

# Fix permissions
RUN usermod -u 1000 www-data

# Composer
RUN php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

# PHPUnit
RUN curl "https://phar.phpunit.de/phpunit.phar" -L -o "phpunit.phar"
RUN chmod +x phpunit.phar
RUN mv phpunit.phar /usr/local/bin/phpunit

# Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug
