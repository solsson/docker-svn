
FROM httpd:2.4.23

ENV SVN_VERSION 1.9.3
ENV SVN_BZ2_URL https://www.apache.org/dist/subversion/subversion-$SVN_VERSION.tar.bz2
ENV SVN_BZ2_SHA1 27e8df191c92095f48314a415194ec37c682cbcf

RUN buildDeps=' \
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
	&& apt-get install -y --no-install-recommends libsqlite3-0 \
	&& apt-get install -y --no-install-recommends $buildDeps \
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
	&& apt-get purge -y --auto-remove $buildDeps \
	&& echo "Include conf/svn/httpd.conf" >> conf/httpd.conf

COPY conf/* conf/svn/

COPY bin/* /usr/local/bin/

VOLUME /svn
