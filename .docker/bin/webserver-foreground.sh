#!/bin/sh

set -euxo pipefail # fail hard

wait-for-it -t $APP_HOST_TIMEOUT $APP_HOST
exec nginx -g 'daemon off;'
