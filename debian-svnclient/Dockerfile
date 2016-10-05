FROM debian:jessie

RUN apt-get update && \
  apt-get install -y curl libsvn-perl subversion && \
  apt-get autoremove -y && \
  apt-get clean
