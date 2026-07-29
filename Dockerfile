FROM php:8.2-apache

# Install PostgreSQL extensions and dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Enable Apache mod_rewrite for URL routing if needed
RUN a2enmod rewrite

# Copy all project files into the container's web directory
COPY . /var/www/html/

# Expose port 80
EXPOSE 80
