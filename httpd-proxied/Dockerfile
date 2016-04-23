FROM solsson/svn-httpd:latest

RUN sed -i 's|^#LoadModule rewrite_module|LoadModule rewrite_module|' conf/httpd.conf

RUN echo "Include conf/svn/proxied/*.conf" >> conf/httpd.conf

ADD proxied conf/svn/proxied

# Other things we could do in this label:
#  * Change log format to use X-Forwarded-For
