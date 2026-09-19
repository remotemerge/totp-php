# Use PHP with Nginx Unit as the base image
FROM serversideup/php:8.1-fpm-nginx

# Switch to root user to perform system-level operations
USER root

# Build arguments
ARG APPLICATION_UID=1000
ARG APPLICATION_GID=1000

# Install required PHP extensions
RUN install-php-extensions xdebug

# Configure user permissions and ownership for the web server
RUN docker-php-serversideup-set-id www-data ${APPLICATION_UID}:${APPLICATION_GID}

# Set the user to match host user for proper file permissions
RUN docker-php-serversideup-set-file-permissions --owner ${APPLICATION_UID}:${APPLICATION_GID} --service nginx

# Create and configure Composer cache directory
RUN mkdir -p /composer/cache && chown -R ${APPLICATION_UID}:${APPLICATION_GID} /composer/cache

# Copy local entrypoint scripts
COPY --chmod=755 ./.docker/scripts/ /etc/entrypoint.d/

# Switch to unprivileged user for security
USER ${APPLICATION_UID}:${APPLICATION_GID}

# Set the working directory for the application
WORKDIR /var/www/html
