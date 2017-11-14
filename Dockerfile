FROM php:7.0-apache

MAINTAINER Kenji Kina <Kenji@analiticavisual.consulting>

RUN apt-get update && apt-get install -y \
        cron \
        mcrypt \
        libmcrypt-dev \
        libcurl4-openssl-dev \
        libbz2-dev \
        libgd-dev \
        libfreetype6-dev \
		libjpeg62-turbo-dev \
		libmcrypt-dev \
		libpng12-dev \
        vim \
        wget \
        unzip \
        git \
        nano \
    && docker-php-ext-install -j$(nproc) curl \
    && docker-php-ext-install -j$(nproc) mcrypt \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql \
    && docker-php-ext-install -j$(nproc) pdo_mysql \
    && docker-php-ext-install -j$(nproc) gd

RUN a2enmod rewrite

COPY . /var/www/html
WORKDIR /var/www/html

RUN chmod 777 admin/sd-system \
    && chmod 777 admin/sd-system/attachments \
    && chmod 777 admin/sd-system/exports \
    && chmod 777 custom/sessions \
    && chmod 777 custom/qrcodes \
    && chmod 777 custom/uploads

EXPOSE 80

COPY config/php.ini /usr/local/etc/php/

RUN chmod 777 /usr/local/etc/php/php.ini