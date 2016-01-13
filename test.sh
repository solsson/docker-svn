#!/bin/sh
docker-compose build
docker-compose up -d

docker exec dockersvn_svnmaster_1 repocreate r1 -o daemon -u 3818ff11-c409-4f9e-acd1-1d9903fc4329
docker exec dockersvn_svnbackup_1 repocreate r1 -o daemon -u 3818ff11-c409-4f9e-acd1-1d9903fc4329
