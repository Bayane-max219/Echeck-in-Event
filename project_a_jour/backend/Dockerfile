# ─────────────────────────────────────────────────────────────────────────────
# Stage 1 : Build — installer les dépendances Composer (sans dev)
# ─────────────────────────────────────────────────────────────────────────────
FROM php:8.2-fpm-alpine AS builder

WORKDIR /var/www/html

# Extensions PHP + outils de build
RUN apk add --no-cache \
    git curl bash zip unzip \
    libpng-dev freetype-dev libjpeg-turbo-dev \
    libxml2-dev oniguruma-dev \
    icu-dev openssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo pdo_mysql mbstring xml gd opcache intl

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Dépendances en premier (cache Docker)
COPY backend/composer.json backend/composer.lock ./
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-scripts \
    --no-interaction \
    --prefer-dist

# Code source complet
COPY backend/ .

# Autoloader optimisé
RUN composer dump-autoload --optimize --no-dev

# ─────────────────────────────────────────────────────────────────────────────
# Stage 2 : Production image (légère)
# ─────────────────────────────────────────────────────────────────────────────
FROM php:8.2-fpm-alpine AS production

WORKDIR /var/www/html

# Runtime uniquement
RUN apk add --no-cache \
    bash mysql-client openssl \
    libpng freetype libjpeg-turbo libxml2 oniguruma icu \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo pdo_mysql mbstring xml gd opcache intl

# Copier l'app buildée
COPY --from=builder /var/www/html /var/www/html

# Permissions var/ (cache, logs)
RUN mkdir -p var/cache var/log config/jwt \
    && chown -R www-data:www-data var config/jwt \
    && chmod -R 775 var config/jwt

# Config PHP prod
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

# Entrypoint (wait DB + migrations JWT + démarrage)
COPY docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
