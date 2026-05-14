# syntax=docker/dockerfile:1.7
FROM php:8.5-fpm AS base

ARG WWW_UID=1000
ARG WWW_GID=1000

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libsqlite3-dev \
        libicu-dev \
    ; \
    docker-php-ext-install zip; \
    docker-php-ext-install pdo_mysql; \
    docker-php-ext-install pdo_sqlite; \
    docker-php-ext-install intl; \
    rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Match host UID/GID so bind-mounted files keep host ownership
RUN set -eux; \
    if id -u www-data >/dev/null 2>&1; then \
        groupmod -g ${WWW_GID} www-data || true; \
        usermod  -u ${WWW_UID} -g ${WWW_GID} www-data || true; \
    fi

WORKDIR /var/www/html

# -----------------------------------------------------------------------------
FROM base AS dev

RUN set -eux; \
    pecl install xdebug; \
    docker-php-ext-enable xdebug

COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini
COPY docker/php/php.ini    /usr/local/etc/php/conf.d/zz-app.ini

# Bake vendor at build time so first `docker compose up` is ready to serve.
# composer.lock is intentionally not committed; if missing, install falls back to update.
COPY composer.json composer.lock* ./
RUN composer install \
        --no-interaction --no-scripts --no-progress --prefer-dist \
        || composer update --no-interaction --no-scripts --no-progress --prefer-dist

# App source is bind-mounted by compose at runtime.

# -----------------------------------------------------------------------------
FROM base AS prod

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini

COPY --chown=www-data:www-data . /var/www/html/
RUN set -eux; \
    composer install --no-dev --no-interaction --no-scripts --no-progress \
        --prefer-dist --optimize-autoloader; \
    chown -R www-data:www-data var/

USER www-data
