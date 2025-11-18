# Use an official PHP image with Apache as the base image.
FROM php:8.2.0-apache

# Set environment variables.
ENV ACCEPT_EULA=Y
LABEL maintainer="jhoyosl@globalsys.co"

# Install system dependencies.
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules required for Laravel.
RUN a2enmod rewrite

# Set the Apache document root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Update the default Apache site configuration
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# PHP
# Install intl extension
RUN apt-get update \
    && apt-get install -y libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) intl

# Install zpi extension
RUN apt-get update \
    && apt-get install -y libzip-dev \
    && docker-php-ext-install zip

RUN docker-php-ext-install \
    pdo_mysql 
# Install mbstring extension
RUN apt-get update \
    && apt-get install -y \
    libxslt-dev \
    && docker-php-ext-install -j$(nproc) \
    dom \
    xsl

# Install SOAP extension
RUN docker-php-ext-install -j$(nproc) \
    soap

# Install mbstring extension
RUN apt-get update \
    && apt-get install -y libonig-dev \
    && docker-php-ext-install -j$(nproc) \
    gettext \
    mbstring

# Install gd extension
RUN docker-php-ext-install gd

# Install Composer globally.
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Create a directory for your Laravel application.
WORKDIR /var/www/html

# Copy the Laravel application files into the container.
COPY . .

# Install Laravel dependencies using Composer.
RUN composer install --no-interaction --optimize-autoloader

# Update composer to latest version
RUN composer update

# Update spatie/laravel-permission package
RUN composer update spatie/laravel-permission

#create storage and cache directories
RUN mkdir storage bootstrap/cache -p 

# Set permissions for Laravel.
RUN chown -R www-data:www-data storage bootstrap/cache

# Set timezone
RUN apt-get update && apt-get install -y tzdata
ENV TZ=America/Bogota
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN composer install && composer update && php artisan storage:link && php artisan cache:clear && php artisan config:clear && composer dump-autoload
# Expose port 80 for Apache.
EXPOSE 80

# Start Apache web server.
# CMD ["php artisan storage:link"]
CMD ["apache2-foreground"]

# You can add any additional configurations or commands required for Laravel 11 here.