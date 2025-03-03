# Use the official PHP image with NGINX
FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    unzip \
    curl \
    git \
    php-cli \
    php-mbstring \
    php-xml \
    php-curl \
    php-zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Set the working directory
WORKDIR /var/www/html

# Copy the application code into the container
COPY . /var/www/html

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Create a non-root user and switch to that user
RUN useradd -ms /bin/bash appuser
USER appuser

# Fix permissions for the Laravel files
RUN chown -R appuser:appuser /var/www/html

# Install application dependencies
RUN composer install --no-dev --optimize-autoloader --prefer-dist

# Set up storage and cache permissions
RUN chmod -R 775 storage bootstrap/cache

# Expose the port the app runs on
EXPOSE 80

# Start PHP-FPM server
CMD ["php-fpm"]
