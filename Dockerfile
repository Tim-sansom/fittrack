# =============================================================================
# FitTrack Web Server Dockerfile
# Base Image: Official PHP 8.4 with Apache
# =============================================================================

FROM php:8.4-apache

# ----------------------------------------------------------------------------
# System Dependencies and PHP Extensions
# ----------------------------------------------------------------------------
# Install necessary system libraries and enable PHP extensions required by 
# the application (PDO, MySQL, mbstring for string handling).
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip mbstring \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ----------------------------------------------------------------------------
# Apache Configuration
# ----------------------------------------------------------------------------
# Enable the mod_rewrite module required for clean URLs and routing.
RUN a2enmod rewrite

# Configure the Apache document root to point to the /public directory.
# This ensures that sensitive files in /config and /includes are not 
# directly accessible via the web server, enforcing the three-tier architecture.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Update the default Apache virtual host to use the new document root.
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Allow .htaccess overrides in the public directory for local routing rules.
RUN echo '<Directory ${APACHE_DOCUMENT_ROOT}>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/fittrack.conf \
    && a2enconf fittrack

# ----------------------------------------------------------------------------
# Application Code
# ----------------------------------------------------------------------------
# Set the working directory for the application.
WORKDIR /var/www/html

# Copy the entire application code into the container.
COPY . /var/www/html/

# Set appropriate permissions for the Apache user (www-data).
# This ensures the web server can read the files and write to the uploads directory.
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/uploads

# Expose port 80 for HTTP traffic.
EXPOSE 80