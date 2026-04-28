FROM node:20-alpine AS frontend-builder

WORKDIR /var/www/html

COPY package*.json ./
RUN npm ci

COPY resources/js ./resources/js
COPY resources/css ./resources/css
COPY vite.config.js ./
COPY public ./public

RUN npm run build

FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    bash \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    mysql-client

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .
COPY --from=frontend-builder /var/www/html/public/build ./public/build

RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

EXPOSE 9000
CMD ["php-fpm"]
