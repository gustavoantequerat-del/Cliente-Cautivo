#!/usr/bin/env bash
# Genera dist/cliente-cautivo.zip listo para subir al cPanel: el Backend con vendor/ y el Frontend ya compilado.
# Úselo cuando el hosting no tiene Composer ni Node. Requiere: php, composer, npm, zip.
set -euo pipefail
cd "$(dirname "$0")/.."
Carpeta_Raiz="$(pwd)"

# 1. Frontend: compila Vue dentro de Backend/public/build
(cd Frontend && npm ci && npm run build)

# 2. Backend: dependencias de producción
cd Backend
composer install --no-dev --optimize-autoloader --no-interaction

# 3. Empaqueta solo la carpeta Backend (es lo que vive en el servidor)
rm -rf "$Carpeta_Raiz/dist" && mkdir -p "$Carpeta_Raiz/dist"
zip -rq "$Carpeta_Raiz/dist/cliente-cautivo.zip" . \
  -x 'tests/*' '.env' 'database/database.sqlite' 'public/hot' \
     'storage/logs/*' 'storage/framework/sessions/*' 'storage/framework/views/*' 'storage/framework/cache/data/*' \
     'storage/app/private/*'

composer install --no-interaction >/dev/null   # restaura dependencias de desarrollo
echo "Listo: dist/cliente-cautivo.zip"
