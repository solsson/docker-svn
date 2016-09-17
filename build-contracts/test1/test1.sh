#!/bin/bash
RETRY="--retry 3 --retry-delay 10"

curl $RETRY -f http://svn/svn/ || exit 1

curl $RETRY -f http://svn/admin/repocreate -d reponame=test1 -d owner=daemon || exit 1

echo "During development the test script will hang here for 1 hour"
sleep 3600
