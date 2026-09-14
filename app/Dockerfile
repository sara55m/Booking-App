# --- Stage 1: build frontend assets with Vite ---
    FROM node:20-alpine AS node-build
    WORKDIR /app

    # Build-time args for Vite (these get baked into the compiled JS bundle,
    # since Vite only reads import.meta.env.VITE_* at BUILD time, not runtime)
    ARG VITE_PUSHER_APP_KEY
    ARG VITE_PUSHER_APP_CLUSTER
    ARG VITE_PUSHER_HOST
    ARG VITE_PUSHER_PORT
    ARG VITE_PUSHER_SCHEME
    ENV VITE_PUSHER_APP_KEY=$VITE_PUSHER_APP_KEY
    ENV VITE_PUSHER_APP_CLUSTER=$VITE_PUSHER_APP_CLUSTER
    ENV VITE_PUSHER_HOST=$VITE_PUSHER_HOST
    ENV VITE_PUSHER_PORT=$VITE_PUSHER_PORT
    ENV VITE_PUSHER_SCHEME=$VITE_PUSHER_SCHEME

    COPY package*.json ./
    RUN npm ci
    COPY . .
    RUN npm run build

    # --- Stage 2: PHP application ---
    FROM php:8.2-cli

    # System dependencies
    RUN apt-get update && apt-get install -y \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libpq-dev \
        && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip \
        && rm -rf /var/lib/apt/lists/*

    # Composer
    COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

    WORKDIR /var/www/html

    # Copy app source
    COPY . .

    # Copy built frontend assets from stage 1
    COPY --from=node-build /app/public/build ./public/build

    # Install PHP dependencies (no dev dependencies, optimized autoloader)
    RUN composer install --no-dev --optimize-autoloader --no-interaction

    # Laravel storage/cache permissions
    RUN mkdir -p storage/framework/{sessions,views,cache} \
        && chmod -R 775 storage bootstrap/cache

    # Render provides $PORT at runtime; default to 8080 for local testing
    ENV PORT=8080
    EXPOSE 8080

    # Cache config/routes/views at container start, then serve
    CMD php artisan config:cache \
        && php artisan route:cache \
        && php artisan view:cache \
        && php artisan serve --host=0.0.0.0 --port=$PORT
