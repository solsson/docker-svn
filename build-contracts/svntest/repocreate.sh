#!/bin/bash
set -e
[[ -z "$DEBUG" ]] || set -x

[ -z "$RETRY" ] & RETRY="--retry 3 --retry-delay 5"

curl $RETRY -f http://svn_adminrest/svn/ -I || exit 1

curl $RETRY -f http://svn_adminrest/admin/repocreate -d reponame=test1 || exit 1

curl $RETRY -f http://svn_adminrest/svn/test1/ -I || exit 1

#noaccess=$(curl $RETRY -f http://svn_noadminrest/admin/repocreate -I)
#echo $noaccess
#echo $noaccess | grep -q "403" || exit 2
# Currently we get 500 from all errors, which causes retries
curl http://svn/admin/repocreate -I | grep -q "500" || exit 1

echo "Really successful? Response codes from the cgi script are very uninformative. See logs until die->500 is fixed, at least."
#echo "During development the test script will hang here for 1 hour"
#sleep 3600
