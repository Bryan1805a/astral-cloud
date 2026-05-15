FROM php:8.2-apache

# Install extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

RUN a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html