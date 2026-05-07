#!/usr/bin/env bash
set -euo pipefail

# Usage: ./scripts/deploy/update_release.sh [git-ref]
# Hostinger shared hosting deployment (~/apps/castbook)
# Note: Node/npm not available on server — frontend assets must be pre-built and committed.

GIT_REF="${1:-main}"
APP_DIR="${HOME}/apps/castbook"

echo ">>> Deploying ${GIT_REF} to ${APP_DIR}"
cd "${APP_DIR}"

echo ">>> Fetching latest code"
git fetch --all --tags --prune
git checkout --force "${GIT_REF}"

echo ">>> Installing PHP dependencies (production)"
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

echo ">>> Running database migrations"
php artisan migrate --force

echo ">>> Clearing and rebuilding caches"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache || true

echo ">>> Restarting queue workers"
php artisan queue:restart || true

echo ">>> Deployment complete: ${GIT_REF} @ $(date)"
echo ">>> App URL: $(php artisan tinker --execute="echo config('app.url');" 2>/dev/null || echo 'see .env APP_URL')"
