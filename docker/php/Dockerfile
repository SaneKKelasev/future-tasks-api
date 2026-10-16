# syntax=docker/dockerfile:1.7

###############################################
# Stage 1: Composer dependencies
###############################################
FROM composer:2 AS vendor

WORKDIR /app

# Сначала только манифесты — так слой с зависимостями кэшируется,
# пока composer.json не менялся.
COPY composer.json composer.lock ./

# --no-dev: без phpunit, faker и пр. в production-образе.
# --no-scripts: не запускаем artisan на этом этапе (нет полного кода).
# --no-autoloader: сгенерируем автолоадер позже, в финальном stage.
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --no-autoloader \
        --prefer-dist

###############################################
# Stage 2: Runtime (php-fpm + nginx-клиент)
###############################################
FROM php:8.3-fpm-alpine AS runtime

# Устанавливаем системные зависимости и PHP-расширения,
# нужные Laravel и MySQL.
RUN apk add --no-cache \
        bash \
        git \
        curl \
        unzip \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
        mysql-client \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        zip \
        intl \
        opcache \
        bcmath

# Composer из официального образа — забираем бинарник.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Создаём пользователя с UID/GID 1000 — совпадает с дефолтным
ARG UID=1000
ARG GID=1000
RUN addgroup -g ${GID} -S app \
    && adduser -u ${UID} -S app -G app

WORKDIR /var/www/html

# Копируем vendor из первого stage и весь код проекта.
COPY --from=vendor /app/vendor ./vendor
COPY . .

# Генерируем оптимизированный автолоадер для production.
RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && chown -R app:app storage bootstrap/cache

USER app

EXPOSE 9000

CMD ["php-fpm"]