FROM php:7.4-fpm

# Arguments defined in docker-compose.yml
ARG uname
ARG gid
ARG uid

# Install system dependencies
RUN apt-get update && apt-get install -y \
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
  cron

RUN docker-php-ext-configure gd \
  --enable-gd \
  --with-jpeg

# php config
ADD ./php.ini /usr/local/etc/php/php.ini

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache mysqli && \
  pecl install xdebug && \
  docker-php-ext-enable mysqli xdebug

# opcache config
ADD opcache.ini "$PHP_INI_DIR/conf.d/opcache.ini"

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer
RUN groupadd --gid $gid $uname
RUN useradd -G www-data,root -s /bin/bash --uid $uid --gid $gid $uname

RUN mkdir -p /home/$uname/.composer && \
  chown -R $uname:$uname /home/$uname

RUN composer create-project roots/bedrock . && \
  composer require wpackagist-plugin/woocommerce

# Set working directory
WORKDIR /var/www/html

USER $uname


