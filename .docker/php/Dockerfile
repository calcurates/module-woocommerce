FROM php:8.0-fpm-alpine

# Update repo and install some utilites
RUN set -xe \
    && apk update \
    && apk upgrade \
    && apk --no-cache add htop unzip gzip git curl mc dcron

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# WP-Cli
RUN set -xe \
    && curl -L -o /wp-cli.phar https://github.com/wp-cli/wp-cli/releases/download/v2.5.0/wp-cli-2.5.0.phar \
    && chmod 755 /wp-cli.phar

# PHP
RUN set -xe \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \

    # Opcache
    && docker-php-ext-install opcache \
    && docker-php-ext-enable opcache \

    # Xdebug
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \

    # ZIP
    && apk add --no-cache libzip libzip-dev \
    && docker-php-ext-install zip \
    && docker-php-ext-enable zip \

    # Mysql
    && apk add --no-cache mysql-client \
    && docker-php-ext-install mysqli \
    && docker-php-ext-enable mysqli \

    # Imagick
    && apk add --no-cache imagemagick libgomp imagemagick-dev \
    && pecl install imagick \
    && docker-php-ext-enable imagick \

    # Exif
    && docker-php-ext-install exif \
    && docker-php-ext-enable exif \

    # Bcmath
    && docker-php-ext-install bcmath \
    && docker-php-ext-enable bcmath

# Cleanup
RUN set -xe \
    && apk del --no-cache .build-deps \
    && apk del --no-cache libzip-dev \
    && apk del --no-cache imagemagick-dev \
    && rm -rf /tmp/* /var/cache/apk/*


WORKDIR /var/www/wordpress

COPY entrypoint.sh /
RUN chmod 755 /entrypoint.sh

# avoid the docker initialization
# see https://github.com/docker/compose/issues/1809
ENTRYPOINT ["/entrypoint.sh"]
EXPOSE 9000
CMD ["php-fpm"]
