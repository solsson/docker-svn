FROM solsson/svn-httpd

ENV RWEB=fpm

ENV RWEB_VERSION=f56ced9062c7ed287b13d61841efb6cd88f5e3fc
ENV RWEB_TGZ_URL=https://github.com/Reposoft/rweb/archive/$RWEB_VERSION.tar.gz
ENV RWEB_TGZ_SHA1=03deacc902cdaee1b63a22da6f528104dc4029cf

RUN echo "Downloading rweb source..." \
  && curl -SLk "$RWEB_TGZ_URL" -o /usr/src/rweb-$RWEB_VERSION.tar.gz \
  && echo "$RWEB_TGZ_SHA1 /usr/src/rweb-$RWEB_VERSION.tar.gz" | sha1sum -c - \
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
