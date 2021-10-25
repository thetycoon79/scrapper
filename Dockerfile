FROM php:8.0-apache

RUN apt update && apt install -y zlib1g-dev g++ libicu-dev zip libzip-dev zip libpq-dev git \
    && docker-php-ext-install intl opcache pdo pdo_mysql mysqli \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

COPY ./src  /var/www/propertyListing

WORKDIR /var/www/propertyListing

RUN a2enmod rewrite
COPY ./apache/apache.conf /etc/apache2/sites-available/propertyListing.conf
RUN a2ensite propertyListing.conf
RUN a2dissite 000-default.conf

RUN chown -R www-data:www-data /var/www/propertyListing
RUN chmod -R 755 /var/www/propertyListing/vendor

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer