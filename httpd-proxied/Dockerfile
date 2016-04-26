FROM solsson/svn-httpd:latest

ENV mod_auth_env_ref=master

ADD https://github.com/Reposoft/mod_auth_env/archive/$mod_auth_env_ref.tar.gz src/
RUN cd src/ && tar -xvf $mod_auth_env_ref.tar.gz && mv mod_auth_env-$mod_auth_env_ref mod_auth_env

RUN buildDeps=' \
		gcc \
  ' \
  && apt-get update \
  && apt-get install -y --no-install-recommends $buildDeps \
  && rm -r /var/lib/apt/lists/* \
  && cd src/mod_auth_env/ \
  && apxs -c -i mod_auth_env.c \
  && apt-get purge -y --auto-remove $buildDeps

RUN echo "Include conf/svn/proxied/*.conf" >> conf/httpd.conf

ADD proxied conf/svn/proxied

# To set env from request headers we need mod_rewrite
RUN sed -i 's|^#LoadModule rewrite_module|LoadModule rewrite_module|' conf/httpd.conf
