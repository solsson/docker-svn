
FROM php:5-fpm

RUN apt-get update && apt-get install -y \
    libcurl3 \
    libcurl4-openssl-dev \
    graphicsmagick \
    locales \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

RUN echo "Enable locale(s) for rweb" \
  && sed -i "s/^#\ \+\(en_US.UTF-8\)/\1/" /etc/locale.gen \
  && locale-gen en_US.UTF-8
ENV LC_ALL en_US.UTF-8

RUN docker-php-ext-install mbstring curl
