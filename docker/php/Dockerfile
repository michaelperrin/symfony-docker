FROM php:7.0-fpm
MAINTAINER Michaël Perrin <contact@michaelperrin.fr>

# Necessary packages for Composer
RUN apt-get update \
    && apt-get install -y \
        git \
        unzip

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
