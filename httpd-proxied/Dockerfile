FROM solsson/svn-httpd:latest

RUN sed -i 's|^#LoadModule rewrite_module|LoadModule rewrite_module|' conf/httpd.conf

RUN echo "Include conf/svn/headers-*.conf" >> conf/httpd.conf

ADD headers-auth.conf conf/svn/
