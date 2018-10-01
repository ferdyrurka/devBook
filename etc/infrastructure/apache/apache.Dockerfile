FROM php:7.2-apache

RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_mysql && a2enmod rewrite && apt-get install -y screen

COPY apache.conf /etc/apache2/sites-enabled/000-default.conf