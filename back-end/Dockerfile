FROM php:7.4-apache

# 1. Install development packages and clean up apt cache.
RUN apt-get update && apt-get install -y \
    curl \
    g++ \
    git \
    libbz2-dev \
    libfreetype6-dev \
    libicu-dev \
    libjpeg-dev \
    libmcrypt-dev \
    libpng-dev \
    libreadline-dev \
    sudo \
    unzip \
    zip \
 && rm -rf /var/lib/apt/lists/*

# 2. Apache configs + document root.
RUN echo "ServerName laravel-app.local" >> /etc/apache2/apache2.conf

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 3. mod_rewrite for URL rewrite and mod_headers for .htaccess extra headers like Access-Control-Allow-Origin-
RUN a2enmod rewrite headers

# 4. Start with base PHP config, then add extensions.
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN docker-php-ext-install \
    fileinfo \
    bcmath \
    bz2 \
    calendar \
    iconv \
    intl \
    opcache \
    pdo_mysql 

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

# Install mbstring extension
RUN apt-get update \
    && apt-get install -y \
    libxml2-dev \
    libxslt-dev \
    && docker-php-ext-install -j$(nproc) \
        dom \
        xmlrpc \
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



# 5. Composer.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy laravel proyect
COPY . /var/www/html/

WORKDIR /var/www/html/

RUN chmod -R 777 /var/www/html/storage/logs
# Instalar proyecto y dependencias
#RUN composer update
#RUN composer install

