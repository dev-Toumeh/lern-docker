FROM php:8.4.5-fpm-bookworm AS base

WORKDIR /var/www/html

RUN apt update && apt install -y vim unzip

COPY --from=ghcr.io/mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions @composer pdo_mysql intl

FROM base AS production
COPY ./ /var/www/html
RUN composer install
RUN chown www-data:www-data ./logs/database.log ./logs/app.log

FROM base AS development
RUN install-php-extensions xdebug
COPY ./docker/php/conf/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini