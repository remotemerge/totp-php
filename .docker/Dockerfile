# Build arguments
ARG PHP_IMAGE_TAG=8.1-unit

# Use PHP with Nginx Unit as the base image
FROM serversideup/php:${PHP_IMAGE_TAG}

# Switch to root user to perform system-level operations
USER root

# Build arguments
ARG USER_ID
ARG GROUP_ID

# Install required PHP extensions
RUN install-php-extensions xdebug

# Configure user permissions and ownership for the web server
RUN docker-php-serversideup-set-id www-data ${USER_ID}:${GROUP_ID}

# Set the user to match host user for proper file permissions
RUN docker-php-serversideup-set-file-permissions --owner ${USER_ID}:${GROUP_ID} --service unit

# Create and configure Composer cache directory
RUN mkdir -p /composer/cache && chown -R www-data:www-data /composer/cache

# Copy local entrypoint scripts
COPY --chmod=755 ./.docker/scripts/ /etc/entrypoint.d/

# Switch to unprivileged user for security
USER www-data

# Set the working directory for the application
WORKDIR /var/www/html
