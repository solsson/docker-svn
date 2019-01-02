FROM debian:stretch-slim@sha256:6c31161e090aa3f62b9ee1414b58f0a352b42b2b7827166e57724a8662fe4b38

ENV SVN_VERSION 1.8.19
ENV SVN_BZ2_URL https://archive.apache.org/dist/subversion/subversion-$SVN_VERSION.tar.bz2
ENV SVN_BZ2_SHA1 51d7e5329ad86a650f8fc806eb68e581055a3fd1

RUN depsRuntime=' \
		libsqlite3-0 \
		ca-certificates \
		curl \
		jq \
		libapr1 \
		libaprutil1 \
		libneon27 \
		libserf-1-1 \
	' \
	&& depsBuild=' \
		bzip2 \
		gcc \
		libpcre++-dev \
		libssl-dev \
		make \
		libsqlite3-dev \
		libz-dev \
		libapr1-dev \
		libaprutil1-dev \
		libneon27-dev \
		libserf-dev \
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
