FROM php:8.1-fpm as php

RUN docker-php-ext-install pdo_mysql
RUN pecl install apcu
RUN apt-get update && \
  apt-get install -y \
  libzip-dev libxslt-dev zip unzip

RUN docker-php-ext-install zip xsl intl
RUN docker-php-ext-enable apcu opcache

WORKDIR /usr/src/app

COPY ./ /usr/src/app

RUN PATH=$PATH:/usr/src/app/vendor/bin:bin
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
