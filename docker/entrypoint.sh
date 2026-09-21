#!/usr/bin/env bash
set -euo pipefail

cd /app

# Port: Railway/Render menyuntik $PORT, VPS pakai 8080
export SERVER_NAME=":${PORT:-8080}"

if [ -z "${APP_KEY:-}" ]; then
  echo "[entrypoint] FATAL: APP_KEY kosong." >&2
  echo "[entrypoint] Generate di lokal: php artisan key:generate --show" >&2
  echo "[entrypoint] Lalu simpan hasilnya sebagai environment variable APP_KEY di dashboard hosting." >&2
  exit 1
fi

mkdir -p storage/framework/cache/data storage/framework/sessions \
         storage/framework/views storage/logs bootstrap/cache storage/app/public

# Migrasi. --force wajib karena APP_ENV=production.
echo "[entrypoint] migrate --force"
php artisan migrate --force

# Symlink public/storage -> storage/app/public (dilewati kalau sudah ada).
echo "[entrypoint] storage:link"
php artisan storage:link || true

# Cache dibangun saat runtime supaya env var dari dashboard ikut terbaca.
echo "[entrypoint] cache config/route/view"
php artisan config:cache
php artisan route:cache || true
php artisan view:cache || true

echo "[entrypoint] siap, listen di ${SERVER_NAME}"
exec "$@"
