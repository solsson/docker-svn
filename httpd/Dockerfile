
FROM httpd:2.4.23

ENV SVN_VERSION 1.9.4
ENV SVN_BZ2_URL https://www.apache.org/dist/subversion/subversion-$SVN_VERSION.tar.bz2
ENV SVN_BZ2_SHA1 bc7d51fdda43bea01e1272dfe9d23d0a9d6cd11c

RUN depsRuntime=' \
		libsqlite3-0 \
	' \
	&& depsBuild=' \
		ca-certificates \
		curl \
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
# TODO
ENV CGI_ADMIN_ACCESS "/.*/"

VOLUME /svn
