#!/bin/bash

# Enable strict error handling
set -euo pipefail

# Install dependencies
composer install --no-interaction --no-progress --optimize-autoloader --prefer-dist
