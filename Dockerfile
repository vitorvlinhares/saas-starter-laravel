# syntax=docker/dockerfile:1

FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

FROM dunglas/frankenphp:1-php8.4 AS app

WORKDIR /app

RUN install-php-extensions \
    pcntl \
    pdo_pgsql \
    pdo_sqlite \
    redis \
    zip \
    opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .
COPY --from=assets /app/public/build ./public/build
COPY docker/Caddyfile /etc/frankenphp/Caddyfile

RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader \
    && php artisan storage:link || true

EXPOSE 80

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
