
FROM php:5-fpm

RUN apt-get update && apt-get install -y \
    libcurl3 \
    graphicsmagick \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install mbstring curl
