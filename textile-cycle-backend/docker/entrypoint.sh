#!/bin/sh
# Script execute a chaque demarrage du conteneur backend.
set -e
cd /var/www/html

# Pas de cle d'application fournie : on en genere une temporaire (les sessions
# seront perdues au redemarrage). En vrai deploiement, fournir APP_KEY.
if [ -z "$APP_KEY" ]; then
  echo "[entrypoint] APP_KEY absente : generation d'une cle temporaire."
  APP_KEY="$(php artisan key:generate --show)"
  export APP_KEY
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage bootstrap/cache

# Lien public/storage (sans erreur s'il existe deja)
php artisan storage:link >/dev/null 2>&1 || true

# Migrations automatiques si RUN_MIGRATIONS=true (utile en demo avec docker compose)
if [ "$RUN_MIGRATIONS" = "true" ]; then
  echo "[entrypoint] Execution des migrations..."
  php artisan migrate --force
fi

exec "$@"
