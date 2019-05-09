# Leeloo multistage

# Stage 0
FROM composer:1.7.0

COPY . /var/www/project
RUN composer global require hirak/prestissimo

# Stage 1
FROM php:fpm

COPY --from=0 /var/www/project /var/www/project

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        zlib1g-dev \
        libxml2-dev \
        openssl \
    && docker-php-ext-install \
        pdo_mysql

RUN apt-get update && apt-get install -y --no-install-recommends libzip-dev && docker-php-ext-install zip

RUN docker-php-ext-configure intl \
       && docker-php-ext-install intl

RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer
RUN composer global require hirak/prestissimo

RUN apt-get install -y --no-install-recommends openssl

WORKDIR /var/www/project/

# Avoid permission issues
RUN mkdir -p /.composer/cache
RUN chmod 777 -R /.composer/cache
RUN chmod 777 -R /var/www/project/var
RUN chmod 777 -R /var/www/project/public/uploads

# Script to wait for DB to be ready
ADD https://github.com/ufoscout/docker-compose-wait/releases/download/2.2.1/wait /wait
RUN chmod +x /wait

CMD /wait && /var/www/project/runDocker.sh && "php-fpm"
