FROM solsson/svn-httpd

ENV RWEB_VERSION=1.6.0
ENV RWEB_TGZ_URL=https://github.com/Reposoft/rweb/archive/v$RWEB_VERSION.tar.gz
ENV RWEB_TGZ_SHA1=c3c7ddb747e0f91130ded4cc25cb26d894a05bd5

RUN echo "Downloading rweb source..." \
  && curl -SLk "$RWEB_TGZ_URL" -o /usr/src/rweb-$RWEB_VERSION.tar.gz \
  && echo "$RWEB_TGZ_SHA1 /usr/src/rweb-$RWEB_VERSION.tar.gz" | sha1sum -c - \
  && mkdir -p /usr/src/rweb \
  && tar -xvf /usr/src/rweb-$RWEB_VERSION.tar.gz -C /usr/src/rweb --strip-components=1 \
  && rm /usr/src/rweb-$RWEB_VERSION.tar.gz

RUN mkdir /opt/rweb \
  && mv /usr/src/rweb/repos-web /opt/rweb/ \
  && mv /usr/src/rweb/repos-plugins /opt/rweb/ \
  && mkdir conf/svn/rweb/ \
  && sed -i 's|^#LoadModule rewrite_module|LoadModule rewrite_module|' conf/httpd.conf \
  && mv /usr/src/rweb/test/conf-svn/rweb-services.conf conf/svn/rweb/services-fpm.conf \
  && echo "Include conf/svn/rweb/*.conf" >> conf/svn/httpd.conf
