FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache mysql-dev git unzip \
    && docker-php-ext-install pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ARG USER_ID=1000
ARG GROUP_ID=1000
RUN addgroup -g ${GROUP_ID} appgroup && \
    adduser -D -u ${USER_ID} -G appgroup appuser

USER appuser