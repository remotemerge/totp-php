#!/bin/bash

# Enable strict error handling
set -euo pipefail

# Set current UID in environment
APPLICATION_UID=$(id -u)
export APPLICATION_UID

# Login to the app container
docker compose --file compose.yml exec --user "${APPLICATION_UID}" web-server bash
