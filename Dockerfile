
FROM httpd

ENV SVN_VERSION 1.9.3
ENV SVN_BZ2_URL https://www.apache.org/dist/subversion/subversion-$SVN_VERSION.tar.bz2

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
	&& apt-get install -y --no-install-recommends $buildDeps \
	&& rm -r /var/lib/apt/lists/* \
	&& curl -SL "$SVN_BZ2_URL" -o svn.tar.bz2 \
	&& curl -SL "$SVN_BZ2_URL.asc" -o svn.tar.bz2.asc \
&& exit 0 \
	&& gpg --verify svn.tar.bz2.asc \
	&& mkdir -p src/svn \
	&& tar -xvf svn.tar.bz2 -C src/svn --strip-components=1 \
	&& rm svn.tar.bz2* \
	&& cd src/svn \
	&& ./configure --enable-so --enable-ssl --prefix=$HTTPD_PREFIX --enable-mods-shared=most \
	&& make -j"$(nproc)" \
	&& make install \
	&& cd ../../ \
	&& rm -r src/svn \
	&& sed -ri ' \
		s!^(\s*CustomLog)\s+\S+!\1 /proc/self/fd/1!g; \
		s!^(\s*ErrorLog)\s+\S+!\1 /proc/self/fd/2!g; \
		' /usr/local/apache2/conf/httpd.conf \
	&& apt-get purge -y --auto-remove $buildDeps
