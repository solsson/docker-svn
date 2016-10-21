#!/bin/bash

./repocreate.sh \
  || exit 0

echo "There were test failures"
exit 1
