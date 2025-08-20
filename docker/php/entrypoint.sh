set -euo pipefail

APP_DIR="/var/www/html"
OVERLAY_DIR="/app_overlay"

echo "=== Bootstrapping Symfony 6.4 skeleton (runtime) ==="
if [ ! -f "${APP_DIR}/composer.json" ]; then
  echo "Creating Symfony skeleton..."
  composer create-project symfony/skeleton:"6.4.*" "${APP_DIR}" --no-interaction
fi

cd "${APP_DIR}"

echo "Requiring dependencies..."
composer require --no-interaction --no-scripts \
  symfony/orm-pack \
  symfony/validator \
  symfony/serializer \
  symfony/cache \
  symfony/runtime \
  symfony/monolog-bundle \
  symfony/http-client

composer require --no-interaction --dev symfony/maker-bundle

echo "Copying overlay (explicit)..."
mkdir -p "${APP_DIR}/config" "${APP_DIR}/src" "${APP_DIR}/migrations"

cp -a "/app_overlay/config/." "${APP_DIR}/config/" 2>/dev/null || true

cp -a "/app_overlay/src/." "${APP_DIR}/src/" 2>/dev/null || true

cp -a "/app_overlay/migrations/." "${APP_DIR}/migrations/" 2>/dev/null || true


rm -f "${APP_DIR}/config/routes/attributes.yaml" "${APP_DIR}/config/routes/annotations.yaml" || true

mkdir -p var/cache var/log

echo "Waiting for Postgres at db:5432..."
for i in {1..40}; do
  if php -r 'fsockopen("db", 5432);' 2>/dev/null; then
    echo "Postgres is up."
    break
  fi
  echo "Retrying ($i/40)..."; sleep 1
done


: "${DATABASE_URL:=postgresql://app:app@db:5432/app?serverVersion=16&charset=utf8}"
export DATABASE_URL

composer dump-autoload --no-interaction

if [ -f "bin/console" ]; then
  echo "Running doctrine:database:create (if not exists)..."
  php bin/console doctrine:database:create --if-not-exists || true

  echo "Running doctrine:migrations:migrate..."
  php bin/console doctrine:migrations:migrate -n || true

  echo "Clearing and warming cache..."
  php bin/console cache:clear
  php bin/console cache:warmup
fi

echo "=== Starting PHP built-in server on 0.0.0.0:8000 ==="
php -S 0.0.0.0:8000 -t public
