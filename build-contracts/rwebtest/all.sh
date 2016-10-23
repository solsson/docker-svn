#!/bin/bash

TESTID="test-$(date +%s)"

./repocreate.sh $TESTID \
  && ./rweb-history.sh $TESTID \
  && exit 0

echo "There were test failures"
exit 1
