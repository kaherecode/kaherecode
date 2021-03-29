FROM php:7.4-fpm as php

RUN docker-php-ext-install pdo_mysql
RUN pecl install apcu
RUN apt-get update && \
  apt-get install -y \
  libzip-dev libxslt-dev

RUN docker-php-ext-install zip xsl
RUN docker-php-ext-enable apcu

WORKDIR /usr/src/app

COPY ./ /usr/src/app

RUN PATH=$PATH:/usr/src/app/vendor/bin:bin
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
