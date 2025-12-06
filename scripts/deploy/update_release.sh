#!/usr/bin/env bash
set -euo pipefail

# Usage: ./scripts/deploy/update_release.sh v1.2.0
# Requires: deploy user with git access, composer, npm, php-cli.

GIT_REF="${1:-main}"
BASE_DIR="/var/www/castbook"
RELEASES_DIR="${BASE_DIR}/releases"
TIMESTAMP="$(date +%Y-%m-%d_%H%M%S)"
RELEASE_DIR="${RELEASES_DIR}/${TIMESTAMP}"

echo ">>> Cloning ${GIT_REF} into ${RELEASE_DIR}"
mkdir -p "${RELEASES_DIR}"
git clone --depth=1 --branch "${GIT_REF}" git@github.com:yourorg/castbook.git "${RELEASE_DIR}"

cd "${RELEASE_DIR}"

echo ">>> Installing composer dependencies"
composer install --no-dev --prefer-dist --optimize-autoloader

echo ">>> Copying shared environment and storage links"
cp "${BASE_DIR}/shared/.env" .env
ln -snf "${BASE_DIR}/shared/storage" "${RELEASE_DIR}/storage"
mkdir -p "${BASE_DIR}/shared/public/uploads"
ln -snf "${BASE_DIR}/shared/public/uploads" "${RELEASE_DIR}/public/uploads"

echo ">>> Running migrations and optimizations"
php artisan migrate --force
php artisan config:cache
php artisan route:cache

echo ">>> Building frontend assets"
npm ci
npm run build

echo ">>> Activating new release"
ln -snf "${RELEASE_DIR}" "${BASE_DIR}/current"

echo ">>> Restarting queue workers"
php artisan queue:restart || true

echo "Deployment complete. Verify application health and logs."
