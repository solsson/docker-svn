
# https://github.com/docker-library/repo-info/blob/master/repos/httpd/tag-details.md
FROM httpd:2.4.25@sha256:0d817a924bed1a216f12a0f4947b5f8a2f173bd5a9cebfe1382d024287154c99

ENV SVN_VERSION 1.9.5
ENV SVN_BZ2_URL https://www.apache.org/dist/subversion/subversion-$SVN_VERSION.tar.bz2
ENV SVN_BZ2_SHA1 8bd6a44a1aed30c4c6b6b068488dafb44eaa6adf

RUN depsRuntime=' \
		libsqlite3-0 \
		curl \
	' \
	&& depsBuild=' \
		ca-certificates \
		bzip2 \
		gcc \
		libpcre++-dev \
		libssl-dev \
		make \
		libsqlite3-dev \
		libz-dev \
	' \
	set -x \
	&& apt-get update \
	&& apt-get install -y --no-install-recommends $depsRuntime \
	&& apt-get install -y --no-install-recommends $depsBuild \
	&& rm -r /var/lib/apt/lists/* \
	&& curl -SL "$SVN_BZ2_URL" -o subversion-$SVN_VERSION.tar.bz2 \
	&& echo "$SVN_BZ2_SHA1 subversion-$SVN_VERSION.tar.bz2" | sha1sum -c - \
	&& mkdir -p src/svn \
	&& tar -xvf subversion-$SVN_VERSION.tar.bz2 -C src/svn --strip-components=1 \
	&& rm subversion-$SVN_VERSION.tar.bz2* \
	&& cd src/svn \
	&& ./configure \
	&& make -j"$(nproc)" \
	&& make install \
	&& /sbin/ldconfig \
	&& cd ../../ \
	&& rm -r src/svn \
	&& apt-get purge -y --auto-remove $depsBuild \
	&& echo "Include conf/svn/httpd.conf" >> conf/httpd.conf

COPY conf conf/svn

# TODO is there a licensing aspect on copy from project source instead of downloading from docker build?
COPY site_perl /usr/local/lib/site_perl
COPY bin/* admin-cgi/
RUN ln -s $(pwd)/admin-cgi/repocreate /usr/local/bin/repocreate

# Set to non-empty to enable /admin/repocreate http endpoint
ENV ADMIN_REST_ACCESS ""
# Set to "anon" to pick up username (for commit log etc) from BASIC auth header without verification of credentials
ENV AUTHN ""
# Set to "svn"/"inrepo"/"admrepo", see conf/authz/options.conf
ENV AUTHZ ""
# Set to "fpm" to enable rweb conf
ENV RWEB ""

# Allow IfDefine based on container runtime env
RUN sed -i 's/-DFOREGROUND/-DFOREGROUND -DAUTHN=$AUTHN -DAUTHZ=$AUTHZ -DRWEB=$RWEB/' /usr/local/bin/httpd-foreground

# httpd config has hard coded /svn for parent path, but if we declare a Volume here you can't prepare repositories in downstream images
#VOLUME /svn

RUN mkdir /svn && chown daemon /svn
