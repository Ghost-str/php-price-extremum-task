FROM php:8.2-cli

ARG COMPOSER_VERSION=2.6.3
COPY setup_composer.sh /
RUN pecl install uopz xdebug && docker-php-ext-enable uopz xdebug \
    && chmod +x ./setup_composer.sh && ./setup_composer.sh && rm ./setup_composer.sh \
    && apt update && apt install -y zip && apt clean

