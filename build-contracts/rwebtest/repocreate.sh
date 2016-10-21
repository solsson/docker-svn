#!/bin/bash
set -e
if [[ ! -z "$DEBUG" ]]; then
  set -x
fi

TESTID=$1
[ -z "$RETRY" ] & RETRY="--retry 3 --retry-delay 5"

echo "Creating test repository $TESTID"

curl $RETRY -f http://svn/svn/ -I || exit 1

curl $RETRY -f http://svn/admin/repocreate -d reponame=$TESTID || exit 1

curl $RETRY -f http://svn/svn/$TESTID/ -I || exit 1
