FROM php:fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
    && docker-php-ext-install \
        pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

COPY . /var/www/project

WORKDIR /var/www/project/

RUN chmod 777 /.composer/cache
