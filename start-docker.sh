#!/bin/bash

# Enable strict error handling
set -euo pipefail

# Stop and remove orphan services
docker compose --file compose.yml down --remove-orphans

# Set current GID/UID in environment variables
APPLICATION_GID=$(id -g)
APPLICATION_UID=$(id -u)
export APPLICATION_GID APPLICATION_UID

# Create the network for the services
docker network create totp-php-network >/dev/null 2>&1 || true

# Start services
docker compose --file compose.yml up --build
