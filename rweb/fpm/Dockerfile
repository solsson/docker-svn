
FROM php:5-fpm

RUN apt-get update && apt-get install -y \
    libcurl3 \
    libcurl4-openssl-dev \
    graphicsmagick \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install mbstring curl

RUN rm /usr/local/etc/php-fpm.d/www.conf
ADD rweb.conf /usr/local/etc/php-fpm.d/
