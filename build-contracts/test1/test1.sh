#!/bin/bash
[ -z "$RETRY" ] & RETRY="--retry 3 --retry-delay 5"

curl $RETRY -f http://svn/svn/ -I || exit 1

curl $RETRY -f http://svn/admin/repocreate -d reponame=test1 -d owner=daemon || exit 1

curl $RETRY -f http://svn/svn/test1/ -I || exit 1

echo "Really successful? Results from the cgi script are very unreliable. See logs."
echo "During development the test script will hang here for 1 hour"
sleep 3600
