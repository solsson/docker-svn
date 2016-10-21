#!/bin/bash
set -e
if [[ ! -z "$DEBUG" ]]; then
  set -x
fi

TESTID=$1
[ -z "$RETRY" ] & RETRY="--retry 3 --retry-delay 5"

curl $RETRY -f http://svn/svn/$TESTID/?rweb=history | grep "History" || exit 1
