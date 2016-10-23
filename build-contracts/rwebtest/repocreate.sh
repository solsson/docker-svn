#!/bin/bash
set -e
[[ -z "$DEBUG" ]] || set -x

TESTID=$1
[ -z "$RETRY" ] & RETRY="--retry 3 --retry-delay 5"

echo "Creating test repository $TESTID"

curl $RETRY -f http://repocreate@svn/svn/ -I

curl $RETRY -f http://repocreate@svn/admin/repocreate -d reponame=$TESTID

curl $RETRY -f http://repocreate@svn/svn/$TESTID/ -I
