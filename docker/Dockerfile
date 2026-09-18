FROM php:8.3-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libpng-dev libonig-dev default-mysql-client \
    && docker-php-ext-install pdo_mysql zip gd bcmath \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Limiti di upload: i video del prodotto arrivano a 100 MB.
COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html

# Le dipendenze vengono installate al primo avvio se mancanti (volume montato in sviluppo).
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
