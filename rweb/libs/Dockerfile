FROM solsson/rweb:fpm-svn

ADD rweb /opt/libs-rweb

RUN php /opt/libs-rweb/lib/smarty/install.php \
  && php /opt/libs-rweb/lib/simpletest/install.php
