FROM solsson/svn-httpd:latest

RUN echo "Include conf/proxied/*.conf" >> conf/httpd.conf

COPY proxied/* conf/proxied/

# To allow access from other services within the perimeter we need anon
RUN sed -i 's|^#LoadModule authn_anon_module|LoadModule authn_anon_module|' conf/httpd.conf
