#!/bin/bash

# Login to the app container
docker compose --file docker-compose.yml exec --user application totp-server bash
