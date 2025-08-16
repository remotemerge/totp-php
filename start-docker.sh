#!/bin/bash

# Stop and remove orphan services
docker compose --file compose.yml down --remove-orphans

# Set current user IDs in the environment
USER_ID=$(id -u)
GROUP_ID=$(id -g)
export USER_ID GROUP_ID

# Create the network for the services
docker network create rm-pkg-network >/dev/null 2>&1 || true

# Start services
docker compose --file compose.yml up --build
