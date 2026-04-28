FROM php:7.4-apache

ARG INSTALL_DEV=true

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        libonig-dev \
        libzip-dev \
        unzip \
        zip \
    && docker-php-ext-install bcmath mbstring pdo_mysql zip \
    && a2enmod rewrite \
    && echo "ServerName localhost" > /etc/apache2/conf-available/server-name.conf \
    && a2enconf server-name \
    && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN if [ "$INSTALL_DEV" = "true" ]; then \
        composer install --prefer-dist --no-interaction --no-progress --no-scripts --no-autoloader; \
    else \
        composer install --prefer-dist --no-dev --no-interaction --no-progress --no-scripts --no-autoloader; \
    fi

COPY . .

RUN composer dump-autoload --optimize --no-interaction

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

EXPOSE 80
