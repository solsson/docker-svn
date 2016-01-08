
FROM httpd

ENV SVN_VERSION 1.9.3
ENV SVN_BZ2_URL https://www.apache.org/dist/subversion/subversion-$SVN_VERSION.tar.bz2
ENV SVN_PEOPLE_URL https://people.apache.org/keys/group/subversion.asc

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
	&& curl -SL "$SVN_PEOPLE_URL" -o subversion.asc \
	&& curl -SL "$SVN_BZ2_URL" -o subversion-$SVN_VERSION.tar.bz2 \
	&& curl -SL "$SVN_BZ2_URL.asc" -o subversion-$SVN_VERSION.tar.bz2.asc \
	&& gpg --import subversion.asc \
	&& gpg --verify subversion-$SVN_VERSION.tar.bz2.asc \
	&& mkdir -p src/svn \
	&& tar -xvf subversion-$SVN_VERSION.tar.bz2 -C src/svn --strip-components=1 \
	&& rm subversion.asc \
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

ADD conf conf/svn/

VOLUME /svn
