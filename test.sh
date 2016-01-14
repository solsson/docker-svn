#!/bin/bash

failures=0
SYNCWAIT="0"

# Failed to execute custom commands with docker-compose so we need to know container name for exec
# This might be a REST service some day
repocreate_svnmaster="docker exec dockersvn_svnmaster_1 repocreate"
repocreate_svnbackup="docker exec dockersvn_svnbackup_1 repocreate"

docker-compose build

echo "# First start of testbed..."
docker-compose up -d

echo "# There were no repositories to sync on start..."
docker logs dockersvn_svnsync_1

echo "# Creating UUID-matching repositories on master and slave..."
$repocreate_svnmaster r1 -o daemon -u 3818ff11-c409-4f9e-acd1-1d9903fc4329 -c 1
$repocreate_svnbackup r1 -o daemon -u 3818ff11-c409-4f9e-acd1-1d9903fc4329 -c 1

echo "# Someone commits something to master..."
svn import docker-compose.yml "http://localhost:30001/svn/r1/$(date +%y%m%d_%H%M%S%N).txt" -m "test"

echo "# Sync job is put on some schedule or replication strategy..."
sleep $SYNCWAIT && echo "# Sync job happens ..."
docker-compose up svnsync

echo "# Master dies and loses all its data..."
docker-compose kill svnmaster && docker-compose rm -f

echo "# Master is resurrected, by a replication controller probably, and gets an empty repo"
docker-compose up -d svnmaster
$repocreate_svnmaster r1 -o daemon -u 3818ff11-c409-4f9e-acd1-1d9903fc4329 -c 1

sleep $SYNCWAIT && echo "# Sync job happens again on next scheduled occasion ..."
docker-compose up svnsync

echo "# Backup service dies and loses all its data..."
docker-compose kill svnbackup && docker-compose rm -f

echo "# Backup goes up, gets an empty repo..."
docker-compose up -d svnbackup
$repocreate_svnbackup r1 -o daemon -u 3818ff11-c409-4f9e-acd1-1d9903fc4329 -c 1

sleep $SYNCWAIT && echo "# Sync job happens again..."
docker-compose up svnsync

echo "# Despite the dying containers our data should be on the master..."
svn log -c 1 "http://$(docker-compose port svnmaster 80)/svn/r1/" || (( failures += 1 ))
echo "# ... and the slave ..."
svn log -c 1 "http://$(docker-compose port svnbackup 80)/svn/r1/" || (( failures += 1 ))

echo "# Destoying testbed..."
docker-compose kill && docker-compose rm -f

if [ $failures -gt 0 ]; then
  echo "# There were assertion failures!";
else
  echo "# Test completed"
fi
exit $failures
