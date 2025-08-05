FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libxpm-dev \
    libgd-dev \
    libmagickwand-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-xpm
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u 1000 -d /home/symfoshop symfoshop
RUN mkdir -p /home/symfoshop/.composer && \
    chown -R symfoshop:symfoshop /home/symfoshop

# Copy existing application directory contents
COPY . /var/www/html

# Copy existing application directory permissions
COPY --chown=symfoshop:symfoshop . /var/www/html

# Create .env file for Docker build
RUN echo "APP_ENV=prod" > .env && \
    echo "APP_SECRET=docker-build-secret-change-in-production" >> .env && \
    echo "DATABASE_URL=mysql://symfoshop:symfoshop123@mysql:3306/symfoshop?serverVersion=8.0" >> .env && \
    echo "APP_DEBUG=false" >> .env && \
    echo "MAILER_DSN=smtp://localhost" >> .env

# Change current user to symfoshop
USER symfoshop

# Install PHP dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader --classmap-authoritative

# Change back to root for file operations
USER root

# Set permissions
RUN chown -R symfoshop:www-data /var/www/html \
    && chmod -R 755 /var/www/html/var \
    && chmod -R 755 /var/www/html/public

# Switch back to symfoshop user
USER symfoshop

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"] 