# Portal Cliente Cautivo · Blockfinity Advisors

MVP del portal privado para instituciones cliente, pensado para correr en un **cPanel compartido**:
Laravel 12 (PHP 8.2+), MySQL, Vue 3 con Inertia y CSS Modules (sin Tailwind), sin Node, Redis ni procesos persistentes en el servidor.

El repositorio está separado en dos carpetas:

| Carpeta | Qué contiene | Se sube al servidor |
|---|---|---|
| `Backend/` | Laravel: rutas, controladores, modelos, base de datos, pruebas | Sí (es lo único que vive en el cPanel) |
| `Frontend/` | Vue 3: páginas, componentes, plantillas y estilos | No: se compila dentro de `Backend/public/build` |

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
# Backend (terminal 1)
cd Backend
composer install
cp .env.example .env
# Para local, cambie en .env: APP_ENV=local, APP_DEBUG=true, DB_CONNECTION=sqlite,
#   SESSION_SECURE_COOKIE=false, MAIL_MAILER=log
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed --seeder=Sembrador_Datos_Demo   # carga la cuenta demo "Banco Demo S.A."
php artisan serve

# Frontend (terminal 2)
cd Frontend
npm install
npm run dev        # o "npm run build" para generar Backend/public/build
```

Usuarios de la demo (**solo para local; no use el seeder demo en producción**):

| Rol | Correo | Contraseña |
|---|---|---|
| Staff / CMS | admin@blockfinity.test | Blockfinity2026 |
| Ejecutiva de cuenta | ejecutiva@blockfinity.test | Blockfinity2026 |
| Cliente (admin de la institución) | maria.rojas@bancodemo.test | Cliente2026demo |
| Cliente (funcionario) | jorge.quiroga@bancodemo.test | Cliente2026demo |

Pruebas: `cd Backend && composer test` (27 pruebas: aislamiento entre instituciones, aprobaciones, verificación, bolsa de horas, enlaces de video y CMS).
Las pruebas también comprueban que cada página que pide el Backend exista en `Frontend/Paginas`.

---

## Despliegue en cPanel

### 1. Requisitos del hosting (revíselos antes)
- **PHP 8.2 o superior** (*MultiPHP Manager*), con las extensiones `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `bcmath`, `ctype`, `tokenizer`, `xml`, `curl`, `zip`.
- **MySQL 5.7+ / MariaDB 10.3+** (se usan columnas JSON).
- **Cron jobs** habilitados.
- Ni Node ni Redis son necesarios.

### 2. Preparar el paquete (en su computadora)
```bash
./Scripts/Empaquetar_Para_Cpanel.sh   # compila el Frontend y genera dist/cliente-cautivo.zip con la carpeta Backend
```
Si el cPanel tiene *Terminal* y Composer, puede clonar el repo con *Git Version Control* y ejecutar
`composer install --no-dev -o` dentro de `Backend/`. En ese caso compile antes el Frontend en su computadora
(`cd Frontend && npm ci && npm run build`) y versione `Backend/public/build`.

### 3. Subir y configurar
1. Suba el zip **fuera de `public_html`** (ej. `/home/USUARIO/cliente-cautivo`) y descomprímalo (contiene el contenido de `Backend/`).
2. En *Dominios*, apunte el dominio o subdominio a `/home/USUARIO/cliente-cautivo/public`.
   - Si no se puede cambiar la raíz, renombre `public/.htaccess.cpanel-raiz` a `.htaccess`, póngalo en la raíz del proyecto y suba el proyecto a la carpeta del dominio.
3. Cree la base de datos y su usuario en *Bases de datos MySQL*.
4. Copie `.env.example` a `.env` y complete `APP_URL`, `DB_*`, `MAIL_*` y `PORTAL_NOTIFY_EMAIL`.
5. En la Terminal (o con un cron de una sola ejecución):
   ```bash
   php artisan key:generate --force
   php artisan migrate --force
   php artisan db:seed --class=Sembrador_Unidades_Negocio --force
   ADMIN_EMAIL=usted@blockfinityadvisors.com php artisan db:seed --class=Sembrador_Administrador --force   # muestra la contraseña
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
Suba los archivos nuevos (o `git pull`; en ese caso el dominio apunta a `.../Backend/public`), luego, dentro de `Backend/`, `php artisan migrate --force` y `php artisan optimize`.

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
Backend/                                 ← Laravel (lo que se sube al cPanel)
  app/Controladores/Portal/              ← pantallas del cliente (Controlador_Proyectos, Controlador_Entregables…)
  app/Controladores/Administracion/      ← CMS genérico, solicitudes de horas, auditoría
  app/Controladores/Acceso/              ← ingreso y recuperación de contraseña
  app/Controladores/Publico/             ← verificación pública del QR de la credencial
  app/Middleware/                        ← cliente/staff, unidad contratada, usuario activo, cabeceras de seguridad
  app/Modelos/                           ← una clase Modelo_* por tabla
  app/Modelos/Rasgos/                    ← aislamiento por institución
  app/Servicios/Servicio_Definicion_Cms.php ← definición declarativa del CMS (agregar campos aquí)
  app/Servicios/Servicio_Enlaces_Video.php  ← Drive/YouTube/Vimeo → URL para incrustar
  config/Configuracion_Portal.php        ← niveles de alianza, límite de subida, correo de avisos
  database/migrations/  database/seeders/  routes/Rutas_Web.php  tests/
Frontend/                                ← Vue 3 + Vite (se compila en Backend/public/build)
  Aplicacion_Principal.js                ← arranque de Inertia
  Paginas/{Acceso,Publico,Portal,Administracion}/Pagina_*.vue
  Plantillas/Plantilla_*.vue  Componentes/Componente_*.vue
  Estilos/Estilos_Globales.css           ← tokens y controles base (cada componente usa <style module>)
Scripts/Empaquetar_Para_Cpanel.sh
```

Roles: `staff` (Blockfinity, ve el CMS), `administrador_institucion` (ve las credenciales de su organización y el registro de accesos) y `funcionario`.

---

## Convención de nombres

Todo lo que escribimos nosotros está en **español** y en **Snake_Pascal_Case** (cada palabra con mayúscula inicial, unidas por `_`).
El **prefijo dice qué es**, así que basta leer el nombre para saber qué hay dentro:

| Qué es | Prefijo / forma | Ejemplo |
|---|---|---|
| Controlador | `Controlador_` + área | `Controlador_Bolsa_Horas` |
| Modelo (una tabla) | `Modelo_` + entidad en singular | `Modelo_Solicitud_Horas` |
| Middleware | `Middleware_` + regla | `Middleware_Exigir_Unidad_Contratada` |
| Servicio (lógica reutilizable) | `Servicio_` + tema | `Servicio_Enlaces_Video` |
| Rasgo (trait) | `Rasgo_` + comportamiento | `Rasgo_Exclusivo_De_Institucion` |
| Seeder | `Sembrador_` + datos | `Sembrador_Datos_Demo` |
| Prueba | `Prueba_` + tema; cada método describe lo que comprueba | `Prueba_Entregables::Aprobar_Exige_La_Contrasena_Correcta` |
| Página Vue | `Pagina_` + pantalla | `Pagina_Detalle_Proyecto.vue` |
| Componente Vue | `Componente_` + qué muestra | `Componente_Panel_Aprobacion.vue` |
| Plantilla Vue (layout) | `Plantilla_` + zona | `Plantilla_Portal.vue` |
| Función / método | Verbo + objeto | `Calcular_Saldo_Horas()`, `Aprobar_Entregable()` |
| Variable / prop | Sustantivo descriptivo | `$Facturas_Pendientes`, `Proximo_Webinar` |
| Booleano | `Es_`, `Esta_`, `Tiene_` | `Es_Staff`, `Esta_Completado`, `Tiene_Archivo` |

**Base de datos**: tablas y columnas en español y en `minusculas_con_guion_bajo` (`solicitudes_horas.horas_estimadas`).
Así se distingue a simple vista un dato guardado (`$Video->fecha_programada`) de un identificador del código (`$Proximo_Webinar`),
y se evitan problemas de mayúsculas de MySQL en Linux. Los campos de formulario usan el mismo nombre que su columna.

**Excepciones** (nombres que exige el framework y no conviene cambiar): las carpetas del esqueleto de Laravel
(`app`, `config`, `database`, `public`, `routes`, `storage`, `tests`…), los métodos que Laravel llama por nombre
(`handle`, `share`, `up`, `run`, `casts`, `booted`), las columnas `id`, `email`, `password`, `remember_token`,
`created_at` y `updated_at`, las tablas internas (`sessions`, `cache`, `jobs`, `password_reset_tokens`),
los nombres de campo del login (`email`, `password`, `password_confirmation`, `current_password`, `token`),
`package.json`, `vite.config.js` y las clases CSS de los estilos.
