FROM debian:jessie@sha256:f7062cf040f67f0c26ff46b3b44fe036c29468a7e69d8170f37c57f2eec1261b

ENV SVN_VERSION 1.8.17
ENV SVN_BZ2_URL https://www.apache.org/dist/subversion/subversion-$SVN_VERSION.tar.bz2
ENV SVN_BZ2_SHA1 0999f5e16b146f824b952a5552826b9cb5c47b13

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
	&& apt-get purge -y --auto-remove $depsBuild
