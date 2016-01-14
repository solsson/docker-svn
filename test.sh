#!/bin/sh

SYNCWAIT="3"

docker-compose build

echo "# First start of testbed..."
docker-compose up -d

echo "# Creating UUID-matching repositories on master and slave..."
docker exec dockersvn_svnmaster_1 repocreate r1 -o daemon -u 3818ff11-c409-4f9e-acd1-1d9903fc4329 -c 1
docker exec dockersvn_svnbackup_1 repocreate r1 -o daemon -u 3818ff11-c409-4f9e-acd1-1d9903fc4329 -c 1

echo "# Someone commits something to master..."
svn import docker-compose.yml "http://localhost:30001/svn/r1/$(date +%y%m%d_%H%M%S%N).txt" -m "test"

echo "# Sync job is put on some schedule or replication strategy..."
sleep $SYNCWAIT && echo "# Sync job happens ..."
docker-compose up -d
docker logs dockersvn_svnsync_1

echo "# Master dies and loses all its data..."
docker kill dockersvn_svnmaster_1 && docker-compose rm -f

echo "# Master is resurrected, by a replication controller probably, and gets an empty repo"
docker-compose up -d
docker exec dockersvn_svnmaster_1 repocreate r1 -o daemon -u 3818ff11-c409-4f9e-acd1-1d9903fc4329 -c 1

sleep $SYNCWAIT && echo "# Sync job happens again on next scheduled occasion ..."
docker-compose up -d
docker logs dockersvn_svnsync_1

echo "# Backup service dies and loses all its data..."
docker kill dockersvn_svnbackup_1 && docker-compose rm -f

echo "# Backup goes up, gets an empty repo..."
docker-compose up -d
docker exec dockersvn_svnbackup_1 repocreate r1 -o daemon -u 3818ff11-c409-4f9e-acd1-1d9903fc4329 -c 1

sleep $SYNCWAIT && echo "# Sync job happens again..."
docker-compose up -d
docker logs dockersvn_svnsync_1

echo "# Despite the dying containers our data should be on the master..."
svn info "http://localhost:30001/svn/r1" | grep "Revision"
echo "# ... and the slave ..."
svn info "http://localhost:30002/svn/r1" | grep "Revision"

echo "# Destoying testbed..."
docker-compose kill && docker-compose rm -f

echo "# Test completed"
