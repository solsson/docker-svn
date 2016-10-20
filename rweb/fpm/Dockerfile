FROM solsson/fpm-svn

RUN apt-get update && apt-get install -y \
    libcurl3 \
    libcurl4-openssl-dev \
    graphicsmagick \
    locales \
    zlib1g-dev \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

RUN echo "Enable locale(s) for rweb" \
  && sed -i "s/^#\ \+\(en_US.UTF-8\)/\1/" /etc/locale.gen \
  && locale-gen en_US.UTF-8
ENV LC_ALL en_US.UTF-8

RUN echo "Install php extensions for rweb" \
  && apt-get update \
  && apt-get install -y gcc make \
  && docker-php-ext-install \
    curl \
    mbstring \
    zip \
  && apt-get purge -y --auto-remove gcc make \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

ADD conf.d/rweb-upload-size.ini /usr/local/etc/php/conf.d/

RUN echo "TODO install rweb at same path as rweb-httpd"
