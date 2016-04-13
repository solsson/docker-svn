
FROM php:5-fpm

RUN apt-get update && apt-get install -y \
  graphicsmagick \
  && apt-get clean \
	&& rm -rf /var/lib/apt/lists/*
