FROM php:8.4-fpm-alpine AS base

RUN apk upgrade --no-cache \
    openssl \
    libcrypto3 \
    libssl3

RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    linux-headers

RUN docker-php-ext-install \
    pdo_mysql \
    zip \
    intl \
    opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

FROM base AS dev

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del $PHPIZE_DEPS

RUN echo "xdebug.mode=off" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY composer.* ./
RUN composer install --no-interaction --no-scripts --no-autoloader

COPY . .
RUN composer dump-autoload --optimize

EXPOSE 9000

CMD ["php-fpm"]

FROM base AS prod

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini

COPY composer.* ./
RUN composer install --no-dev --no-interaction --no-scripts --optimize-autoloader

COPY . .
RUN mkdir -p var \
    && composer dump-autoload --optimize --classmap-authoritative \
    && chown -R www-data:www-data var

EXPOSE 9000

CMD ["php-fpm"]
