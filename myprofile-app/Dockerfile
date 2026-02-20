FROM php:8.3-fpm

# Dependências e extensões
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libicu-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring zip intl gd opcache

# (Opcional) Xdebug para debug
# RUN pecl install xdebug && docker-php-ext-enable xdebug

# Composer no container
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
# o código será montado via volume no docker-compose (não copia aqui)
