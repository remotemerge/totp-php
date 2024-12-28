#!/bin/bash

# Stop and remove orphan services
docker compose --file docker-compose.yml down --remove-orphans

# Remove dangling services
docker container prune -f
docker network prune -f
docker builder prune -f
docker image prune -f
docker volume prune -f

# Start services
docker compose --file docker-compose.yml up
