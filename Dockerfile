FROM php:8.1.0-fpm

RUN apt-get update && apt upgrade -y && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

RUN apt-get update
RUN apt-get install nano -y
RUN apt-get install zip -y
RUN apt-get install unzip -y
RUN apt-get install -y mariadb-client
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install mysqli


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

COPY . /var/www
WORKDIR /var/www
