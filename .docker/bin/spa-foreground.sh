#!/bin/sh

set -euxo pipefail # fail hard

exec nginx -g 'daemon off;'
