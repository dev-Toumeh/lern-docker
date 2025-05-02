FROM httpd:2.4.63-bookworm AS base

COPY ./docker/apache/conf/httpd.conf /usr/local/apache2/conf/httpd.conf
COPY ./docker/apache/conf/httpd-vhosts.conf /usr/local/apache2/conf/extra/httpd-vhosts.conf

FROM base AS production
COPY ./public /usr/local/apache2/htdocs

FROM base AS development
