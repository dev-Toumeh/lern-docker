FROM php:apache-bookworm

WORKDIR /var/www/html

RUN apt update && apt install -y vim unzip

COPY ./docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

COPY --from=ghcr.io/mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions @composer pdo_mysql intl
