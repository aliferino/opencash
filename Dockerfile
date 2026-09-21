# OpenCash — image produksi (FrankenPHP + PHP 8.4)
# Satu image ini dipakai di Railway / Render / Fly / VPS. Tidak terkunci ke satu platform.

# ---------- 1. Aset frontend (Vite) ----------
FROM node:22-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ---------- 2. Dependensi PHP ----------
FROM dunglas/frankenphp:1-php8.4-bookworm AS builder
RUN install-php-extensions pdo_mysql gd intl zip opcache bcmath
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --optimize-autoloader
COPY . .
COPY --from=assets /app/public/build ./public/build
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative

# ---------- 3. Runtime ----------
FROM dunglas/frankenphp:1-php8.4-bookworm AS runtime
RUN install-php-extensions pdo_mysql gd intl zip opcache bcmath
WORKDIR /app
COPY --from=builder /app /app
COPY Caddyfile /etc/frankenphp/Caddyfile
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh \
 && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views \
             storage/logs bootstrap/cache storage/app/public \
 && chown -R www-data:www-data storage bootstrap/cache

# Port diambil dari $PORT (Railway/Render) atau default 8080 (VPS)
EXPOSE 8080
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
