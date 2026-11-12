FROM php:8.4-apache

RUN docker-php-ext-install \
        mysqli \
        pdo_mysql \
    && a2enmod rewrite

COPY docker/apache/000-default.conf \
     /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

EXPOSE 80