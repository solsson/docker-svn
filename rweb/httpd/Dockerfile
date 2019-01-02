FROM solsson/svn-httpd

ENV RWEB_VERSION=v1.7.8
ENV RWEB_TGZ_URL=https://github.com/Reposoft/rweb/archive/$RWEB_VERSION.tar.gz
ENV RWEB_TGZ_SHA256=bcc26e36b12ec594be55f484a70837c489af8dd56dfbf0eb3f75ae5e367161a4

RUN echo "Downloading rweb source..." \
  && curl -SLk "$RWEB_TGZ_URL" -o /usr/src/rweb-$RWEB_VERSION.tar.gz \
  && echo "$RWEB_TGZ_SHA256 /usr/src/rweb-$RWEB_VERSION.tar.gz" | sha256sum -c - \
  && mkdir -p /usr/src/rweb \
  && tar -xf /usr/src/rweb-$RWEB_VERSION.tar.gz -C /usr/src/rweb --strip-components=1 \
  && rm /usr/src/rweb-$RWEB_VERSION.tar.gz \
  && mkdir /opt/rweb \
  && mv /usr/src/rweb/repos-web /opt/rweb/ \
  && mv /usr/src/rweb/repos-plugins /opt/rweb/

RUN  sed -i 's|^#LoadModule rewrite_module|LoadModule rewrite_module|' conf/httpd.conf \
  && sed -i 's|^#LoadModule proxy_module|LoadModule proxy_module|' conf/httpd.conf \
  && sed -i 's|^#LoadModule proxy_fcgi_module|LoadModule proxy_fcgi_module|' conf/httpd.conf \
  && mv /usr/src/rweb/test/conf-svn/rweb-services.conf conf/svn/rweb/services-fpm.conf \
  && echo "Include conf/svn/rweb/*.conf" >> conf/svn/httpd.conf

CMD [ "-DAUTHN=", "-DAUTHZ=", "-DRWEB=fpm" ]
