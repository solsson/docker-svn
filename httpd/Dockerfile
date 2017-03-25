
# https://github.com/docker-library/repo-info/blob/master/repos/httpd/tag-details.md
FROM httpd:2.4.25@sha256:4612fba4347bd87eaeecd5c522d844f26cc4065b45eef9291277497946b7a86c

ENV SVN_VERSION 1.8.17
ENV SVN_BZ2_URL https://www.apache.org/dist/subversion/subversion-$SVN_VERSION.tar.bz2
ENV SVN_BZ2_SHA1 0999f5e16b146f824b952a5552826b9cb5c47b13

ENV DEBIAN_FRONTEND noninteractive

# we have the stretch package repo from upstream, so let's use https://packages.debian.org/stretch/ca-certificates
ENV CA_CERTIFICATES_VERSION 20161130

RUN depsRuntime=" \
		libsqlite3-0 \
		ca-certificates=$CA_CERTIFICATES_VERSION \
		curl \
		libserf-1-1 \
	" \
	&& depsBuild=" \
		ca-certificates \
		bzip2 \
		gcc \
		libpcre++-dev \
		libssl-dev=$OPENSSL_VERSION \
		make \
		libsqlite3-dev \
		libz-dev \
		libneon27-dev \
		libserf-dev \
	" \
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
