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
| Acceso con correo y contraseña; revocación inmediata | Instituciones con sus productos contratados |
| Inicio: nivel de alianza, productos activos y por descubrir, webinar, horas y facturas | Usuarios, credenciales y roles |
| **Ecosistema Blockfinity**: productos con página o video, contratados resaltados y botón Solicitar | Productos del ecosistema y solicitudes de los clientes |
| **Aprobación de entregables** con contraseña, código de verificación, IP y auditoría | Entregables (archivo ≤10 MB o enlace de Drive + código de verificación) |
| Bóveda de entregables + **verificación de documentos en el navegador** (el archivo no se sube) | Videos y webinars por enlace, con vista previa |
| Research (solo si tienen el producto RESEARCH contratado) | Reportes de Research (generales o exclusivos) |
| Academia: webinars en vivo (inscripción + `.ics`), grabados y píldoras con reproductor embebido | Facturas (PDF + XML del SIAT) y contratos |
| Facturación y contratos con descargas | Bolsa de horas: movimientos y atención de solicitudes |
| Bolsa de horas: saldo, vencimientos y solicitudes de consulta o emergencia | Registro de auditoría |
| Credencial digital con **QR de verificación pública** y niveles de alianza | |

Todo el contenido está aislado por institución: un cliente nunca ve datos de otra entidad (cubierto por pruebas).

---

## Desarrollo local

Requisitos: PHP 8.2+, Composer 2, Node 20+ y pnpm (`corepack enable`).

```bash
# Backend (terminal 1)
cd Backend
composer install
cp .env.example .env
# Para local, cambie en .env: APP_ENV=local, APP_DEBUG=true, DB_CONNECTION=sqlite,
#   SESSION_SECURE_COOKIE=false, MAIL_MAILER=log
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed --class=Sembrador_Banco_Nacional_Bolivia                       # catálogo del ecosistema + la institución
ADMIN_EMAIL=usted@blockfinityadvisors.com php artisan db:seed --class=Sembrador_Administrador   # superadmin (imprime la contraseña)
php artisan serve

# Frontend (terminal 2)
cd Frontend
pnpm install       # la primera vez crea pnpm-lock.yaml: versiónelo
pnpm run dev       # o "pnpm run build" para compilar dentro de Backend/public/build
```

No hay datos de demostración: todo se carga desde el CMS. Con el superadmin cree el usuario ejecutivo, asígnele el
Banco Nacional de Bolivia S.A., marque sus productos contratados y cree sus usuarios (o use
`ADMIN_DELEGADO_EMAIL=persona@bnb.com.bo php artisan db:seed --class=Sembrador_Banco_Nacional_Bolivia` para su administrador delegado).

Pruebas: `cd Backend && composer test` (aislamiento entre instituciones, ecosistema y solicitudes, niveles, aprobaciones, verificación, bolsa de horas, enlaces de video y CMS).
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
(`cd Frontend && pnpm install && pnpm run build`) y versione `Backend/public/build`.

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
   php artisan db:seed --class=Sembrador_Banco_Nacional_Bolivia --force    # catálogo del ecosistema + la institución
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

### "Page Expired" (error 419) al ingresar

El navegador no devolvió la cookie de sesión. Revise, en este orden:

1. **El sitio debe abrirse con `https://`.** Con `SESSION_SECURE_COOKIE=true` el navegador no guarda la sesión en `http://`.
   Active SSL en cPanel (*SSL/TLS Status → Run AutoSSL*). Solo para probar sin SSL: `SESSION_SECURE_COOKIE=false`.
2. **`APP_URL`** igual a la dirección real (mismo dominio, con o sin `www`, con `https`). Deje `SESSION_DOMAIN` vacío.
3. **Tabla de sesiones:** `php artisan migrate --force` (con `SESSION_DRIVER=database`).
4. **`storage/` y `bootstrap/cache/` con permiso de escritura** (755 en carpetas).
5. Después de cambiar `.env`: `php artisan optimize:clear && php artisan optimize`.

Si la sesión simplemente venció (página abierta más de `SESSION_LIFETIME` minutos), el portal vuelve al formulario con
el aviso "Su sesión expiró" y el segundo intento funciona.

---

## Reportes de Research y PDFs (visor de Google Drive)

Los PDF de Research, webinars y píldoras se leen dentro del portal con el **visor de Google Drive**:

1. Suba el PDF a Google Drive y compártalo como **"Cualquier persona con el enlace"**.
2. En el CMS pegue ese enlace (Reportes de Research, o Webinars y píldoras con contenido **PDF**).
3. El portal lo muestra con el visor de Google, más un enlace "Abrir en Google Drive" por si la red bloquea el visor.

El servidor no descarga ni guarda los PDF. Las opciones de descarga e impresión son las que el propietario del archivo
permita en Drive (en Drive → Compartir → ⚙︎ se puede desactivar "descargar, imprimir y copiar" para lectores).
En webinars y píldoras en PDF, la persona pulsa **"Marcar como leído"** para registrar su avance.

---

## Certificados

El superadmin o el usuario ejecutivo los suben en **CMS → Certificados**: persona, título, descripción, puntaje y el PDF.
La persona lo ve en **Mis acreditaciones** (puede abrirlo y guardarlo, porque es suyo) junto con el puntaje que obtuvo.
Su administrador delegado ve los certificados de todo su equipo.

---

## Marca de cada institución

En **CMS → Instituciones**, cada institución tiene su color principal, su color de acento y su logo (PNG, JPG o WEBP), con una vista previa del portal en el formulario.
Sus usuarios ven el portal con esos colores y con su logo junto al de Blockfinity. Los tonos claros y oscuros se calculan solos a partir del color principal (`Frontend/Utilidades/Paleta_Marca.js`), y un color muy claro se oscurece para que el texto se lea.
El login es el mismo para todos, porque antes de ingresar no se sabe a qué institución pertenece la persona.

---

## Niveles de alianza

El único incentivo es el nivel de la institución, según cuántos productos del **Ecosistema Blockfinity** tiene contratados:

| Nivel | Productos activos | Credencial | Beneficios |
|---|---|---|---|
| **Cliente Explorador** | 1 | Plomo | Webinars abiertos, prueba guiada de una herramienta, soporte local en 24 h |
| **Aliado Institucional** | 2 | Azul | Webinar trimestral exclusivo, 3 cupos para el Foro, ejecutiva de cuenta asignada |
| **Premium** | 3 o más | Azul marino y plata | Research a medida y benchmark, mesa cerrada con reguladores, línea prioritaria y comité trimestral |

En **Credencial y beneficios** se ve el avance ("2 de 3 productos activos para Premium", "1 producto más para Premium") y los beneficios que aún se desbloquean. Nombres, umbrales y beneficios se ajustan en `config/Configuracion_Portal.php`.

---

## Ecosistema Blockfinity

Reemplaza a los antiguos proyectos. Cada producto (CMS → Ecosistema → Productos, solo superadmin) tiene título, subtítulo,
descripción, imagen, **página** (landing `https`) y, opcional, **video explicativo** (Drive, YouTube o Vimeo).

- **Portal**: tarjetas con imagen, título, subtítulo, **Solicitar** y **Ver más**. Lo contratado se resalta en verde y va primero.
- **Detalle** (clic en la imagen o el título): título, subtítulo, descripción, **Solicitar**, **Ver más** y el video si lo hay.
- **Solicitar** (solo el administrador delegado): queda registrada en CMS → Solicitudes de productos y llega un correo al
  *correo de contacto* (CMS → Configuración; si está vacío, `PORTAL_NOTIFY_EMAIL`) con copia al usuario ejecutivo a cargo.
- **Habilitar**: el ejecutivo marca el producto en la institución (CMS → Instituciones → Productos contratados). La solicitud
  pasa sola a "Habilitado" y el producto cuenta para el nivel de alianza.
- **Ver más** abre la landing del producto en una pestaña nueva (no se incrusta en el portal). Si el producto no tiene
  landing, "Ver más" lleva al detalle, que muestra la descripción y el video explicativo si lo hay.

---

## Cómo se publican los videos (BYOS)

1. Suba el video a Google Drive, compártalo como **"Cualquier persona con el enlace · Lector"** y copie el enlace.
2. En el CMS, en **Videos y webinars → Nuevo**, pegue el enlace. Se acepta Drive, YouTube (incluye "no listado") y Vimeo (incluye el hash de los privados).
3. El sistema guarda **solo el proveedor y el ID** (nunca HTML de iframe) y arma la URL de embed. La política CSP solo permite iframes de esos tres dominios.

**Limitaciones conocidas de Drive**, que conviene explicar a quien cargue contenido:
- El enlace es público para quien lo tenga: quien vea el video puede abrirlo directamente en Drive.
- Drive no informa cuánto se reprodujo. Por eso el avance se registra con **"Marcar como visto"**, que da los puntos una sola vez.
- Algunas redes bancarias bloquean Drive. Para webinars importantes, **YouTube no listado** o **Vimeo con restricción de dominio** son más fiables.

Documentos pesados: súbalos también a Drive y pegue el enlace en el entregable. Use el botón **"Calcular código"** con el archivo original para que la verificación siga funcionando: el código se calcula en su navegador y el archivo no se sube.

---

## Estructura

```
Backend/                                 ← Laravel (lo que se sube al cPanel)
  app/Controladores/Portal/              ← pantallas del cliente (Controlador_Ecosistema, Controlador_Entregables…)
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

Accesos separados por seguridad: los clientes ingresan por `/login` y el staff por `/admin/login` (con límite más estricto de intentos). Una cuenta que usa la puerta que no le corresponde recibe el mismo error que una contraseña incorrecta y queda registrada en la auditoría.

Roles:

| Rol | Qué hace |
|---|---|
| `superadmin` | Personal de Blockfinity con todo el CMS: todas las instituciones, contenido general (videos, research), configuración y auditoría. |
| `usuario_ejecutivo` | Personal de Blockfinity a cargo de instituciones: en el CMS ve y administra solo las suyas (sus datos, colores y logo, productos contratados, solicitudes de productos, usuarios, entregables, certificados, facturas y horas). Las instituciones que crea quedan a su cargo. No puede eliminarlas. |
| `administrador_delegado` | Responsable de la institución en el portal: facturación, bolsa de horas, aprobación de entregables y **solicitud de productos** del ecosistema. |
| `usuario_institucional` | Portal simple: Ecosistema Blockfinity (sin solicitar), píldoras y webinars, research, sus certificados y su credencial. |

El **código de verificación** de un documento es un código único que se calcula a partir del archivo (SHA-256): si el archivo cambia, el código cambia. No es una firma digital ni un dato biométrico.

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
| Seeder | `Sembrador_` + datos | `Sembrador_Banco_Nacional_Bolivia` |
| Prueba | `Prueba_` + tema; cada método describe lo que comprueba | `Prueba_Entregables::Aprobar_Exige_La_Contrasena_Correcta` |
| Página Vue | `Pagina_` + pantalla | `Pagina_Detalle_Producto.vue` |
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
