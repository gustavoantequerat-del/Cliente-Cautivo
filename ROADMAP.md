# Roadmap · Portal Cliente Cautivo

El MVP cubre lo que funciona de forma fiable en un cPanel compartido. El resto del diseño se incorpora por fases.
Cada fase es independiente y no obliga a rehacer lo anterior: cada una agrega sus columnas con una migración nueva.

## Fase 1 · MVP (este repositorio) ✅
Acceso con correo y contraseña; aislamiento por institución; unidades contratadas; Inicio; Proyectos con fases, bitácora y entregables;
aprobación con re-autenticación, código de verificación y auditoría; verificación de documentos en el navegador; Research; Academia con videos BYOS;
facturación y contratos; bolsa de horas; credencial con QR de verificación; niveles de alianza; CMS.

## Fase 1.5 · Endurecimiento (poco esfuerzo, recomendado antes de abrir a clientes)
- [ ] **2FA con TOTP** (Google Authenticator). Se implementa en PHP puro (RFC 6238) o con `pragmarx/google2fa`.
- [ ] Invitación por correo con enlace para crear contraseña, en lugar de que el admin la defina.
- [ ] Notificaciones por correo: nuevo entregable por aprobar, factura por vencer, webinar mañana. Requiere un comando programado.
- [ ] Búsqueda global (MySQL FULLTEXT sobre entregables, research y videos).
- [ ] Exportar el registro de accesos a CSV (cliente admin).
- [ ] Vencimiento automático de horas (movimiento negativo al pasar `fecha_vencimiento`).

## Fase 2 · Funciones del diseño que no requieren infraestructura nueva
- [ ] **Propuestas de valor** (pantalla 05): tablero por etapa, versiones, comentarios y aceptación con contraseña y código de verificación, igual que los entregables.
- [ ] **Presentaciones y formación** (07): archivo de sesiones por área, matriz de cobertura área × unidad y certificados en PDF (dompdf) con QR verificable.
- [ ] **Laboratorio de herramientas** (08): catálogo con enlaces externos y formulario "Solicitar demo".
- [ ] Encuesta "Vote el próximo reporte": un voto por usuario en MySQL (índice único).
- [ ] Insignias internas (imagen + fecha), en lugar de POAP.
- [ ] Ledger de puntos de alianza (tabla de eventos) en lugar del contador simple.
- [ ] Acuerdos y compromisos compartidos, y pulso de satisfacción en proyectos.
- [ ] **Sellado en blockchain vía Stamping.io (API REST)**: un *job* en cola envía el código de verificación al aprobar y guarda la transacción en una columna nueva `entregables.transaccion_sello`. Requiere que el hosting permita HTTP saliente.
- [ ] Tablero de Research en vivo: iframe de Power BI o Looker Studio (mismo patrón BYOS), o gráfico Chart.js desde un CSV.

## Fase 3 · Requiere otra infraestructura o proveedor
| Función del diseño | Por qué no va en cPanel compartido | Camino sugerido |
|---|---|---|
| Acceso con credencial NFT / wallet / soulbound | Contrato, gas, nodo RPC y custodia de llaves privadas en un hosting compartido es un riesgo | Emitir el NFT con un proveedor externo y guardar `direccion_billetera` / `id_token` en columnas nuevas de `usuarios`. El login sigue siendo correo + 2FA |
| Passkeys (WebAuthn) | Necesita librerías y extensiones (gmp/sodium) que no siempre están | `laragear/webauthn` cuando el hosting lo permita |
| Asistente IA con fuentes (RAG) | Embeddings, búsqueda vectorial y streaming; los timeouts de PHP rompen SSE | API de Claude sin streaming + FULLTEXT de MySQL como recuperador; o un microservicio aparte |
| Votaciones on-chain, sandbox de tokenización | Son productos completos, no pantallas | Enlazar a las herramientas de Labs/Build |
| Notificaciones en tiempo real | WebSockets necesitan procesos persistentes | Polling cada pocos minutos o un servicio externo (Pusher) |
| Medición real de reproducción de videos | Drive no tiene API de reproductor | YouTube no listado (IFrame API) o Vimeo |
