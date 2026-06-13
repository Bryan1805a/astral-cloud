FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    libxml2-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    cron \
    && docker-php-ext-install pdo pdo_mysql mysqli zip dom mbstring curl \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite proxy proxy_http proxy_wstunnel

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY proxy-console.conf /etc/apache2/conf-available/proxy-console.conf
RUN a2enconf proxy-console

WORKDIR /var/www/html

# Copy composer files and install dependencies
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --prefer-dist