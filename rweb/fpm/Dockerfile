FROM solsson/fpm-svn

ENV RWEB_VERSION=v1.7.8
ENV RWEB_TGZ_URL=https://github.com/Reposoft/rweb/archive/$RWEB_VERSION.tar.gz
ENV RWEB_TGZ_SHA256=bcc26e36b12ec594be55f484a70837c489af8dd56dfbf0eb3f75ae5e367161a4

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl ca-certificates \
    libcurl3 \
    libcurl4-openssl-dev \
    ghostscript \
    graphicsmagick \
    ghostscript \
    locales \
    zlib1g-dev \
    libicu57 \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

RUN echo "Enable locale(s) for rweb" \
  && sed -i "s/^#\ \+\(en_US.UTF-8\)/\1/" /etc/locale.gen \
  && locale-gen en_US.UTF-8
ENV LC_ALL en_US.UTF-8

RUN echo "Install php extensions for rweb" \
  && apt-get update \
  && apt-get install -y gcc make libicu-dev g++ \
  && docker-php-ext-install \
    curl \
    mbstring \
    zip \
    intl \
  && apt-get purge -y --auto-remove gcc make libicu-dev g++ \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

ADD conf.d/* /usr/local/etc/php/conf.d/

RUN echo "Downloading rweb source..." \
  && curl -SLk "$RWEB_TGZ_URL" -o /usr/src/rweb-$RWEB_VERSION.tar.gz \
  && echo "$RWEB_TGZ_SHA256 /usr/src/rweb-$RWEB_VERSION.tar.gz" | sha256sum -c - \
  && mkdir -p /usr/src/rweb \
  && tar -xf /usr/src/rweb-$RWEB_VERSION.tar.gz -C /usr/src/rweb --strip-components=1 \
  && rm /usr/src/rweb-$RWEB_VERSION.tar.gz \
  && mkdir /opt/rweb \
  && mv /usr/src/rweb/repos-web /opt/rweb/ \
  && mv /usr/src/rweb/repos-plugins /opt/rweb/

RUN php /opt/rweb/repos-web/lib/smarty/install.php

ENV REPOS_TEMP=/tmp
