FROM php:7.4-fpm-buster

# Install system dependencies
RUN apt-get update && apt-get upgrade -y && apt-get install -y \
  git \
  curl \
  dpkg-dev \
  libpng-dev \
  libjpeg-dev \
  libonig-dev \
  libxml2-dev \
  libpq-dev \
  libzip-dev \
  zip \
  unzip \
  cron \
  default-mysql-client \
  mc


# PHP
RUN docker-php-ext-configure gd \
  --enable-gd \
  --with-jpeg && \
  docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip mysqli && \
  pecl install xdebug && \
  docker-php-ext-enable mysqli xdebug


# Composer & WP-Cli
RUN set -xe \
    && curl -L -o /composer.phar https://github.com/composer/composer/releases/download/2.0.13/composer.phar \
    && chmod 755 /composer.phar \
    && curl -L -o /wp-cli.phar https://github.com/wp-cli/wp-cli/releases/download/v2.4.0/wp-cli-2.4.0.phar \
    && chmod 755 /wp-cli.phar

# Cleanup
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/*


WORKDIR /var/www/wordpress

COPY entrypoint.sh /
RUN chmod 755 /entrypoint.sh

# avoid the docker initialization
# see https://github.com/docker/compose/issues/1809
ENTRYPOINT ["/entrypoint.sh"]
EXPOSE 9000
CMD ["php-fpm"]
