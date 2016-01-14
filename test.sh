#!/bin/sh
docker-compose build
docker-compose up -d

docker exec dockersvn_svnmaster_1 repocreate r1 -o daemon -u 3818ff11-c409-4f9e-acd1-1d9903fc4329 -c 1
docker exec dockersvn_svnbackup_1 repocreate r1 -o daemon -u 3818ff11-c409-4f9e-acd1-1d9903fc4329 -c 1

svn import docker-compose.yml "http://localhost:30001/svn/r1/$(date +%y%m%d_%H%M%S%N).txt" -m "test"
docker-compose up -d
docker logs dockersvn_svnsync_1

# docker-compose kill && docker-compose rm -f
