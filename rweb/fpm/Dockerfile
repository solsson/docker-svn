FROM solsson/fpm-svn

ENV RWEB_VERSION=1.6.0
ENV RWEB_TGZ_URL=https://github.com/Reposoft/rweb/archive/v$RWEB_VERSION.tar.gz
ENV RWEB_TGZ_SHA1=c3c7ddb747e0f91130ded4cc25cb26d894a05bd5

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
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

ADD conf.d/* /usr/local/etc/php/conf.d/

RUN echo "Downloading rweb source..." \
  && curl -SLk "$RWEB_TGZ_URL" -o /usr/src/rweb-$RWEB_VERSION.tar.gz \
  && echo "$RWEB_TGZ_SHA1 /usr/src/rweb-$RWEB_VERSION.tar.gz" | sha1sum -c - \
  && mkdir -p /usr/src/rweb \
  && tar -xf /usr/src/rweb-$RWEB_VERSION.tar.gz -C /usr/src/rweb --strip-components=1 \
  && rm /usr/src/rweb-$RWEB_VERSION.tar.gz \
  && mkdir /opt/rweb \
  && mv /usr/src/rweb/repos-web /opt/rweb/ \
  && mv /usr/src/rweb/repos-plugins /opt/rweb/

RUN php /opt/rweb/repos-web/lib/smarty/install.php
