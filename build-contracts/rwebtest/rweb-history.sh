#!/bin/bash
set -e
[[ -z "$DEBUG" ]] || set -x

TESTID=$1
[ -z "$RETRY" ] & RETRY="--retry 3 --retry-delay 5"

curl $RETRY -f http://historytest@svn/svn/$TESTID/?rweb=history | grep "History"
