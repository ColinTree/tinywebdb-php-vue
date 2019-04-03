FROM php:7.3-apache

RUN a2enmod rewrite

RUN docker-php-ext-install mysqli && \
    docker-php-ext-enable mysqli