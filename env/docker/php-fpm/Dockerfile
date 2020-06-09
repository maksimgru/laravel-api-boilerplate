ARG PHP_VERSION=${PHP_VERSION}

FROM php:${PHP_VERSION}-fpm

# Install packages for required extensions
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    zip \
    unzip \
    libzip-dev \
    libz-dev \
    libpq-dev \
    libssl-dev \
    libmcrypt-dev \
    libicu-dev \
    libxml2-dev \
    zlib1g-dev \
    libpng-dev \
    libjpeg-dev\
    libfreetype6-dev\
    libmagickwand-dev \
    libc-client-dev \
    libkrb5-dev \
    libpq-dev\
    libjpeg62-turbo-dev \
    libxpm-dev \
    libvpx-dev \
    curl \
    iputils-ping \
    vim \
    cron \
    supervisor \
  && rm -rf /var/lib/apt/lists/*

# Install Laravel required extensions
RUN docker-php-ext-install json && \
    docker-php-ext-install mbstring && \
    docker-php-ext-install xml && \
    docker-php-ext-install ctype && \
    docker-php-ext-install tokenizer && \
    docker-php-ext-install pgsql && \
    docker-php-ext-install pdo && \
    docker-php-ext-install pdo_pgsql && \
    docker-php-ext-install -j$(nproc) gd && \
    docker-php-ext-install exif && \
    docker-php-ext-install bcmath && \
    docker-php-ext-install opcache && \
    docker-php-ext-install zip && \
    docker-php-ext-configure intl && docker-php-ext-install intl && \
    docker-php-ext-install bcmath && \
    docker-php-ext-install sockets

RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/ && \
    docker-php-ext-install -j$(getconf _NPROCESSORS_ONLN) gd

# Install required PECL extensions
RUN pecl install \
    redis

# Enable above
RUN docker-php-ext-enable redis && \
    docker-php-ext-enable gd

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

###########################################################################
# xDebug:
###########################################################################

ARG PHP_XDEBUG_ENABLE=false
ARG PHP_XDEBUG_VERSION=
RUN if [ ${PHP_XDEBUG_ENABLE} = true ]; then \
    # Install the xdebug extension
    # when php is 7.3, xdebug version has to be xdebug-2.7.0
    pecl install "xdebug${PHP_XDEBUG_VERSION}" && \
    docker-php-ext-enable xdebug \
;fi

# Copy xdebug configuration for remote debugging
COPY ./xdebug.ini /usr/local/etc/php/conf.d/

ARG PHP_XDEBUG_REMOTE_CONNECT_BACK=false
RUN if [ ${PHP_XDEBUG_REMOTE_CONNECT_BACK} = true ]; then \
    echo "xdebug.remote_connect_back=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
;fi

###########################################################################
# Check PHP version:
###########################################################################

ARG PHP_VERSION=${PHP_VERSION}

RUN php -v | head -n 1 | grep -q "PHP ${PHP_VERSION}."

###########################################################################
# Copy PHP configuration files
###########################################################################

COPY ./php.ini /usr/local/etc/php/
COPY ./xlaravel.pool.conf /usr/local/etc/php-fpm.d/

###########################################################################
# Crontab
###########################################################################

COPY ./crontab/laradock /etc/cron.d
RUN chmod 0644 /etc/cron.d/laradock && mkdir -p /var/log/cron && touch /var/log/cron/cron.log
RUN crontab /etc/cron.d/laradock

COPY supervisor/laraworker.conf /etc/supervisor/conf.d/laraworker.conf
RUN chmod 0777 /etc/supervisor/conf.d/laraworker.conf && mkdir -p /var/log/supervisor && touch /var/log/supervisor/laraworker.log

USER root

###########################################################################
# Clean up
###########################################################################

RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && \
    rm /var/log/lastlog /var/log/faillog

RUN usermod -u 1000 www-data

WORKDIR /var/www
