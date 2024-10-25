#!/bin/bash

DOCKER_COMPOSE="docker-compose"
NEW_DOCKER_VERSION=0

docker-compose &> /dev/null || NEW_DOCKER_VERSION=$?

if [ $NEW_DOCKER_VERSION -ne 0 ]; then
    DOCKER_COMPOSE="docker-compose"
fi

echo $DOCKER_COMPOSE
