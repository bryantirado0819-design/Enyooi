FROM php:8.1-apache

# Install extensions and tools
RUN apt-get update && apt-get install -y libzip-dev unzip git mariadb-client libpng-dev libjpeg-dev libfreetype6-dev ffmpeg     && docker-php-ext-install pdo pdo_mysql zip gd opcache     && a2enmod rewrite headers

# Composer (optional)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
# Copy code is done by volume in docker-compose - use mounting for convenience
