#!/usr/bin/env bash

source ./.env

# spa
API_HOST=www.hace.local


docker build -f Dockerfile \
    --build-arg TIMEZONE=$TIMEZONE \
    --tag ${REMOTE_REPO_ADDR}/srigi/hace/app \
    .

docker build -f Dockerfile.webserver \
    --build-arg WEBSERVER_NGINX_PORT=$WEBSERVER_NGINX_PORT \
    --tag ${REMOTE_REPO_ADDR}/srigi/hace/webserver \
    .

docker build -f Dockerfile.spa \
    --build-arg API_HOST=$API_HOST \
    --build-arg SPA_NGINX_PORT=$SPA_NGINX_PORT \
    --tag ${REMOTE_REPO_ADDR}/srigi/hace/spa \
    .

docker push ${REMOTE_REPO_ADDR}/srigi/hace/app
docker push ${REMOTE_REPO_ADDR}/srigi/hace/webserver
docker push ${REMOTE_REPO_ADDR}/srigi/hace/spa
