#!/usr/bin/env bash
set -e

# ------- php.ini overrides a partir de variables de entorno -------
UPLOAD_MAX_FILESIZE="${PHP_UPLOAD_MAX_FILESIZE:-128M}"
POST_MAX_SIZE="${PHP_POST_MAX_SIZE:-128M}"
MEMORY_LIMIT="${PHP_MEMORY_LIMIT:-512M}"
MAX_EXECUTION_TIME="${PHP_MAX_EXECUTION_TIME:-120}"

echo "upload_max_filesize=${UPLOAD_MAX_FILESIZE}" > /usr/local/etc/php/conf.d/uploads.ini
echo "post_max_size=${POST_MAX_SIZE}"            >> /usr/local/etc/php/conf.d/uploads.ini
echo "memory_limit=${MEMORY_LIMIT}"              >> /usr/local/etc/php/conf.d/uploads.ini
echo "max_execution_time=${MAX_EXECUTION_TIME}"  >> /usr/local/etc/php/conf.d/uploads.ini

# Opcional: opcache recomendado en prod
cat >/usr/local/etc/php/conf.d/opcache-recommended.ini <<'EOF'
opcache.enable=1
opcache.enable_cli=1
opcache.validate_timestamps=0
opcache.preload_user=www-data
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
EOF

# ------- espera a MySQL -------
echo "Esperando a MySQL en ${DB_HOST:-db}:${DB_PORT:-3306}..."
until mysqladmin ping -h "${DB_HOST:-db}" -P "${DB_PORT:-3306}" -u"${DB_USERNAME:-rodae}" -p"${DB_PASSWORD:-secret}" --silent; do
  sleep 2
done
echo "MySQL OK."

cd /var/www/html

# ------- dependencias composer si faltan -------
if [ ! -f vendor/autoload.php ]; then
  echo "Instalando dependencias Composer..."
  composer install --no-dev --prefer-dist --optimize-autoloader
fi

# ------- claves y caches -------
if [ -z "$APP_KEY" ] || ! grep -q "APP_KEY=" .env; then
  echo "Generando APP_KEY..."
  php artisan key:generate --force
fi

php artisan storage:link || true
php artisan config:cache || true
php artisan route:cache  || true
php artisan view:cache   || true

# ------- migraciones (opcional) -------
if [ "${MIGRATE_ON_START}" = "true" ] || [ "${MIGRATE_ON_START}" = "1" ]; then
  echo "Ejecutando migraciones..."
  php artisan migrate --force
fi

# Permisos (por si el volumen los machacó)
chown -R www-data:www-data storage bootstrap/cache || true

echo "Arrancando PHP-FPM..."
exec "$@"
