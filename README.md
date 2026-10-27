# Portal Cliente Cautivo · Blockfinity Advisors

MVP del portal privado para instituciones cliente, pensado para correr en un **cPanel compartido**:
Laravel 12 (PHP 8.2+), MySQL, Vue 3 con Inertia y CSS Modules (sin Tailwind), sin Node, Redis ni procesos persistentes en el servidor.

Los videos **no se suben al servidor** (BYOS): se publican en Google Drive, YouTube o Vimeo y en el CMS solo se pega el enlace.

> Diseño de referencia: canvas "Portal Cliente Cautivo". Qué quedó fuera y por qué: [ROADMAP.md](ROADMAP.md).

---

## Qué incluye el MVP

| Portal cliente | CMS (staff Blockfinity, `/admin`) |
|---|---|
| Acceso con correo y contraseña; revocación inmediata | Instituciones con sus unidades contratadas |
| Inicio: nivel de alianza, horas, webinar, facturas, unidades activas y por descubrir, proyectos | Usuarios, credenciales y roles |
| Proyectos: fases, entregables, bitácora | Proyectos, fases y bitácora |
| **Aprobación de entregables** con contraseña, huella SHA-256, IP y auditoría | Entregables (archivo ≤10 MB o enlace de Drive + huella) |
| Bóveda de entregables + **verificación de documentos en el navegador** (el archivo no se sube) | Videos y webinars por enlace, con vista previa |
| Research (solo si tienen la unidad RESEARCH contratada) | Reportes de Research (generales o exclusivos) |
| Academia: webinars en vivo (inscripción + `.ics`), grabados y píldoras con reproductor embebido | Facturas (PDF + XML del SIAT) y contratos |
| Facturación y contratos con descargas | Bolsa de horas: movimientos y atención de solicitudes |
| Bolsa de horas: saldo, vencimientos y solicitudes de consulta o emergencia | Registro de auditoría |
| Credencial digital con **QR de verificación pública** y niveles de alianza | |

Todo el contenido está aislado por institución: un cliente nunca ve datos de otra entidad (cubierto por pruebas).

---

## Desarrollo local

Requisitos: PHP 8.2+, Composer 2 y Node 20+.

```bash
composer install
npm install
cp .env.example .env
# Para local, cambie en .env: APP_ENV=local, APP_DEBUG=true, DB_CONNECTION=sqlite,
#   SESSION_SECURE_COOKIE=false, MAIL_MAILER=log
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed          # carga la cuenta demo "Banco Demo S.A."
composer dev                        # php artisan serve + vite
```

Usuarios de la demo (**solo para local; no use el seeder demo en producción**):

| Rol | Correo | Contraseña |
|---|---|---|
| Staff / CMS | admin@blockfinity.test | Blockfinity2026 |
| Ejecutiva de cuenta | ejecutiva@blockfinity.test | Blockfinity2026 |
| Cliente (admin de la institución) | maria.rojas@bancodemo.test | Cliente2026demo |
| Cliente (funcionario) | jorge.quiroga@bancodemo.test | Cliente2026demo |

Pruebas: `composer test` (26 pruebas: aislamiento entre instituciones, aprobaciones, verificación, bolsa de horas, enlaces de video y CMS).

---

## Despliegue en cPanel

### 1. Requisitos del hosting (revíselos antes)
- **PHP 8.2 o superior** (*MultiPHP Manager*), con las extensiones `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `bcmath`, `ctype`, `tokenizer`, `xml`, `curl`, `zip`.
- **MySQL 5.7+ / MariaDB 10.3+** (se usan columnas JSON).
- **Cron jobs** habilitados.
- Ni Node ni Redis son necesarios.

### 2. Preparar el paquete (en su computadora)
```bash
./scripts/empaquetar-cpanel.sh      # genera dist/cliente-cautivo.zip con vendor/ y assets compilados
```
Si el cPanel tiene *Terminal* y Composer, puede clonar el repo con *Git Version Control* y ejecutar
`composer install --no-dev -o` allí. Los assets compilados (`public/build`) ya están versionados en el repo.

### 3. Subir y configurar
1. Suba el zip **fuera de `public_html`** (ej. `/home/USUARIO/cliente-cautivo`) y descomprímalo.
2. En *Dominios*, apunte el dominio o subdominio a `/home/USUARIO/cliente-cautivo/public`.
   - Si no se puede cambiar la raíz, renombre `public/.htaccess.cpanel-raiz` a `.htaccess`, póngalo en la raíz del proyecto y suba el proyecto a la carpeta del dominio.
3. Cree la base de datos y su usuario en *Bases de datos MySQL*.
4. Copie `.env.example` a `.env` y complete `APP_URL`, `DB_*`, `MAIL_*` y `PORTAL_NOTIFY_EMAIL`.
5. En la Terminal (o con un cron de una sola ejecución):
   ```bash
   php artisan key:generate --force
   php artisan migrate --force
   php artisan db:seed --class=BusinessUnitSeeder --force
   ADMIN_EMAIL=usted@blockfinityadvisors.com php artisan db:seed --class=AdminSeeder --force   # muestra la contraseña
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```
6. Permisos: `storage/` y `bootstrap/cache/` con escritura (755/775).
7. Active **SSL** (AutoSSL) y fuerce HTTPS: las cookies de sesión son seguras.

### 4. Cron (uno solo, cada minuto)
```
* * * * * cd /home/USUARIO/cliente-cautivo && php artisan schedule:run >> /dev/null 2>&1
```
Procesa la cola de correos en lotes que terminan solos (no hay `queue:work` permanente) y limpia la auditoría con más de 18 meses.
Si su PHP de consola no es 8.2+, use la ruta completa, por ejemplo `/opt/cpanel/ea-php82/root/usr/bin/php`.

### 5. Actualizar
Suba los archivos nuevos (o `git pull`), luego `php artisan migrate --force` y `php artisan optimize`.

---

## Cómo se publican los videos (BYOS)

1. Suba el video a Google Drive, compártalo como **"Cualquier persona con el enlace · Lector"** y copie el enlace.
2. En el CMS, en **Videos y webinars → Nuevo**, pegue el enlace. Se acepta Drive, YouTube (incluye "no listado") y Vimeo (incluye el hash de los privados).
3. El sistema guarda **solo el proveedor y el ID** (nunca HTML de iframe) y arma la URL de embed. La política CSP solo permite iframes de esos tres dominios.

**Limitaciones conocidas de Drive**, que conviene explicar a quien cargue contenido:
- El enlace es público para quien lo tenga: quien vea el video puede abrirlo directamente en Drive.
- Drive no informa cuánto se reprodujo. Por eso el avance se registra con **"Marcar como visto"**, que da los puntos una sola vez.
- Algunas redes bancarias bloquean Drive. Para webinars importantes, **YouTube no listado** o **Vimeo con restricción de dominio** son más fiables.

Documentos pesados: súbalos también a Drive y pegue el enlace en el entregable. Use el botón **"Calcular huella"** con el archivo original para que la verificación siga funcionando: la huella se calcula en su navegador y el archivo no se sube.

---

## Estructura

```
app/
  Admin/Resources.php          ← definición declarativa del CMS (agregar campos aquí)
  Http/Controllers/Portal/     ← pantallas del cliente
  Http/Controllers/Admin/      ← CMS genérico, solicitudes de horas, auditoría
  Http/Middleware/             ← cliente/admin, unidad contratada, usuario activo, cabeceras de seguridad
  Models/Concerns/             ← aislamiento por institución
  Support/VideoEmbed.php       ← Drive/YouTube/Vimeo → embed
config/portal.php              ← niveles de alianza, temas, especialidades, límite de subida
resources/js/
  Layouts/  Components/  Pages/{Auth,Portal,Admin,Public}
resources/css/app.css          ← tokens y controles base (cada componente usa <style module>)
```

Roles: `admin` (staff, ve el CMS), `client_admin` (administrador de la institución: ve las credenciales de su organización y el registro de accesos) y `client` (funcionario).
