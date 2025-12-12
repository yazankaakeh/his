# Stage 1: Build frontend assets
FROM node:18-alpine as build-frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY webpack.mix.js ./
COPY resources ./resources
COPY public ./public
# If there are other folders needed for build, add them here
# Using production build
RUN npm run prod

# Stage 2: Main Application
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    icu-dev \
    oniguruma-dev

# Configure PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        bcmath \
        gd \
        intl \
        zip \
        opcache \
        pcntl \
        exif

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/start-container

# Make entrypoint executable
RUN chmod +x /usr/local/bin/start-container

# Workdir
WORKDIR /var/www/html

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .
COPY --from=build-frontend /app/public/css /var/www/html/public/css
COPY --from=build-frontend /app/public/js /var/www/html/public/js
COPY --from=build-frontend /app/mix-manifest.json /var/www/html/mix-manifest.json

# Install PHP dependencies
# Using --no-dev for production image, but can be adjusted
RUN composer install --optimize-autoloader --no-dev --no-interaction --prefer-dist

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["start-container"]

CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
