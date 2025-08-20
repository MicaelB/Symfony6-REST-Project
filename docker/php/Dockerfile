FROM php:8.2-cli-alpine

RUN apk add --no-cache git unzip bash rsync libpq-dev libzip-dev $PHPIZE_DEPS \
    && docker-php-ext-install pdo pdo_pgsql opcache \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_DIR=/var/www/html
WORKDIR ${APP_DIR}

COPY . /app_overlay

COPY docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8000
CMD ["/entrypoint.sh"]
