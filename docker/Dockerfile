# ── Stage 1: Build frontend assets ───────────────────────────────────────────
FROM node:22-alpine AS node-builder

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --silent

COPY resources/ resources/
COPY vite.config.js postcss.config.js tailwind.config.js ./

RUN npm run build

# ── Stage 2: Production PHP image ────────────────────────────────────────────
FROM php:8.4-fpm-alpine AS production

ARG WWWGROUP=82
ARG WWWUSER=82

ENV TZ=Europe/Belgrade
ENV APP_ENV=production

WORKDIR /var/www/html

# System deps + PHP extensions
RUN apk add --no-cache \
        nginx \
        supervisor \
        curl \
        git \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libwebp-dev \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
        libxml2-dev \
        postgresql-dev \
        libpq \
        tzdata \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        gd \
        zip \
        bcmath \
        intl \
        mbstring \
        xml \
        pcntl \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis opcache \
    && apk del --no-cache \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libwebp-dev \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
        libxml2-dev \
        postgresql-dev \
    && rm -rf /var/cache/apk/* /tmp/* \
    && ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install PHP deps (without dev, without running scripts yet)
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# Copy application code
COPY . .

# Copy built frontend assets
COPY --from=node-builder /app/public/build public/build/

# Composer post-install scripts (auto-discover packages, etc.)
RUN composer run-script post-autoload-dump

# Ensure storage dirs exist
RUN mkdir -p storage/logs \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copy Docker config files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/99-production.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/start-container /usr/local/bin/start-container
RUN chmod +x /usr/local/bin/start-container \
    && mkdir -p /var/log/supervisor /run/nginx

EXPOSE 80

ENTRYPOINT ["start-container"]
