FROM php:8.5-fpm

# Update repo and install some utilites
RUN apt-get update && apt-get upgrade -y && apt-get install -y --no-install-recommends \
    htop \
    mc \
    acl \
    file \
    gettext \
    git \
    mariadb-client \
    curl \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# WP-Cli
RUN set -xe \
    && curl -L -o /wp-cli.phar https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod 755 /wp-cli.phar

# https://github.com/mlocati/docker-php-extension-installer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# PHP
RUN set -eux; \
    install-php-extensions \
        intl \
        imagick \
        zip \
        pdo_mysql \
        mysqli \
        pcntl \
        exif \
        bcmath \
        xdebug \
    ;

# Cleanup
RUN set -eux; \
    rm -rf /tmp/*


WORKDIR /var/www/wordpress

COPY entrypoint.sh /
RUN chmod 755 /entrypoint.sh

# avoid the docker initialization
# see https://github.com/docker/compose/issues/1809
ENTRYPOINT ["/entrypoint.sh"]
EXPOSE 9000
CMD ["php-fpm"]
