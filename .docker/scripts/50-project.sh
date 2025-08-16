#!/bin/bash

# Exit on error
set -e

# Install dependencies
/usr/local/bin/php /usr/bin/composer install --no-interaction --no-progress --optimize-autoloader --prefer-dist
