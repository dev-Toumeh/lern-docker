FROM php:8.4.5-fpm-bookworm

WORKDIR /var/www/html

RUN apt update && apt install -y vim unzip

COPY --from=ghcr.io/mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions @composer pdo_mysql intl
