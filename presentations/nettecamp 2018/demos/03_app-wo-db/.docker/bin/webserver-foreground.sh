#!/bin/sh

set -euxo pipefail # fail hard

wait-for-it -t 30 $FCGI_HOST
exec nginx -g 'daemon off;'
