#!/bin/bash

# Set current user IDs in the environment
USER_ID=$(id -u)
GROUP_ID=$(id -g)
export USER_ID GROUP_ID

# Login to the app container
docker compose --file compose.yml exec --user "${USER_ID}" totp-server bash
