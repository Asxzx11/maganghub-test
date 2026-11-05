FROM php:8.4-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

CMD ["sh", "-c", "php artisan config:cache && php artisan migrate --force && php artisan db:seed --force && exec apache2-foreground"]
