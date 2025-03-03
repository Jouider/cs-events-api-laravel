# Use an official PHP image as the base image
FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    git \
    curl \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy the application files into the container
COPY . .

# Install application dependencies (no dev dependencies for production)
RUN composer install --no-dev --optimize-autoloader --prefer-dist

# Set proper permissions for the storage and cache directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Generate Laravel app key
RUN php artisan key:generate

# Expose the app on port 80
EXPOSE 80

# Set up NGINX
FROM nginx:alpine

# Copy the NGINX configuration file into the container
COPY nginx/default.conf /etc/nginx/conf.d/

# Set working directory to the application
WORKDIR /var/www/html

# Copy the app from the previous image into this image
COPY --from=0 /var/www/html /var/www/html

# Run NGINX in the foreground
CMD ["nginx", "-g", "daemon off;"]
