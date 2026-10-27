#!/usr/bin/env bash
# Genera dist/cliente-cautivo.zip listo para subir al cPanel (con vendor/ y assets compilados).
# Úselo cuando el hosting no tiene Composer ni Node. Requiere: php, composer, npm, zip.
set -euo pipefail
cd "$(dirname "$0")/.."

npm ci && npm run build
composer install --no-dev --optimize-autoloader --no-interaction

rm -rf dist && mkdir -p dist
zip -rq dist/cliente-cautivo.zip . \
  -x '.git/*' 'node_modules/*' 'dist/*' 'tests/*' '.env' 'database/database.sqlite' \
     'storage/logs/*' 'storage/framework/sessions/*' 'storage/framework/views/*' 'storage/framework/cache/data/*' \
     'storage/app/private/*'

composer install --no-interaction >/dev/null   # restaura dependencias de desarrollo
echo "Listo: dist/cliente-cautivo.zip"
