<?php

namespace App\Admin;

use App\Models;
use App\Support\VideoEmbed;

/**
 * Definición declarativa del CMS. Cada recurso genera listado + formulario
 * sin escribir una pantalla por tabla. Para agregar un campo: añadirlo aquí.
 *
 * Tipos de campo: text, email, url, textarea, number, decimal, date, datetime, select,
 * boolean, belongsTo, file, password, hash, video_url, units.
 */
class Resources
{
    public static function all(): array
    {
        $institution = ['type' => 'belongsTo', 'model' => Models\Institution::class, 'labelColumn' => 'name'];
        $unit = ['type' => 'belongsTo', 'model' => Models\BusinessUnit::class, 'labelColumn' => 'code'];
        $kinds = config('portal.deliverable_kinds');

        return [
            'instituciones' => [
                'label' => 'Instituciones', 'singular' => 'institución', 'model' => Models\Institution::class,
                'search' => ['name', 'tax_id'], 'order' => ['name', 'asc'], 'group' => 'Cuentas',
                'fields' => [
                    ['name' => 'name', 'label' => 'Nombre', 'type' => 'text', 'required' => true, 'table' => true],
                    ['name' => 'slug', 'label' => 'Identificador (slug)', 'type' => 'text', 'required' => true, 'unique' => true],
                    ['name' => 'legal_name', 'label' => 'Razón social', 'type' => 'text'],
                    ['name' => 'tax_id', 'label' => 'NIT', 'type' => 'text', 'table' => true],
                    ['name' => 'billing_email', 'label' => 'Correo de facturación', 'type' => 'email'],
                    ['name' => 'billing_address', 'label' => 'Dirección de facturación', 'type' => 'text'],
                    ['name' => 'account_manager_id', 'label' => 'Ejecutiva/o de cuenta', 'type' => 'belongsTo',
                        'model' => Models\User::class, 'labelColumn' => 'name', 'where' => ['role', 'admin'], 'table' => true],
                    ['name' => 'alliance_points', 'label' => 'Puntos de alianza', 'type' => 'number', 'table' => true, 'default' => 0],
                    ['name' => 'is_active', 'label' => 'Acceso activo', 'type' => 'boolean', 'table' => true, 'default' => true],
                    ['name' => 'units', 'label' => 'Unidades contratadas', 'type' => 'units'],
                ],
            ],
            'usuarios' => [
                'label' => 'Usuarios y credenciales', 'singular' => 'usuario', 'model' => Models\User::class,
                'search' => ['name', 'email', 'area'], 'order' => ['name', 'asc'], 'group' => 'Cuentas',
                'filter' => 'institution_id',
                'fields' => [
                    ['name' => 'name', 'label' => 'Nombre completo', 'type' => 'text', 'required' => true, 'table' => true],
                    ['name' => 'email', 'label' => 'Correo', 'type' => 'email', 'required' => true, 'unique' => true, 'table' => true],
                    ['name' => 'password', 'label' => 'Contraseña', 'type' => 'password',
                        'help' => 'Mínimo 10 caracteres. Déjela vacía al editar para no cambiarla.'],
                    ['name' => 'role', 'label' => 'Rol', 'type' => 'select', 'required' => true, 'table' => true, 'default' => 'client',
                        'options' => ['client' => 'Funcionario', 'client_admin' => 'Administrador de la institución', 'admin' => 'Staff Blockfinity (CMS)']],
                    ['name' => 'institution_id', 'label' => 'Institución', 'table' => true,
                        'help' => 'Vacío solo para staff de Blockfinity.'] + $institution,
                    ['name' => 'area', 'label' => 'Área', 'type' => 'text', 'table' => true],
                    ['name' => 'position', 'label' => 'Cargo', 'type' => 'text'],
                    ['name' => 'phone', 'label' => 'Teléfono', 'type' => 'text'],
                    ['name' => 'booking_url', 'label' => 'Enlace para agendar (Calendly, Google)', 'type' => 'url',
                        'help' => 'Solo para ejecutivas/os de cuenta.'],
                    ['name' => 'credential_number', 'label' => 'N.º de credencial', 'type' => 'text', 'table' => true, 'readonly' => true],
                    ['name' => 'is_active', 'label' => 'Credencial activa', 'type' => 'boolean', 'table' => true, 'default' => true,
                        'help' => 'Al desactivarla se cierra su sesión y deja de verificarse el QR.'],
                ],
            ],
            'unidades' => [
                'label' => 'Unidades de negocio', 'singular' => 'unidad', 'model' => Models\BusinessUnit::class,
                'search' => ['code', 'name'], 'order' => ['sort', 'asc'], 'group' => 'Cuentas',
                'fields' => [
                    ['name' => 'code', 'label' => 'Código', 'type' => 'text', 'required' => true, 'unique' => true, 'table' => true],
                    ['name' => 'name', 'label' => 'Nombre', 'type' => 'text', 'required' => true, 'table' => true],
                    ['name' => 'description', 'label' => 'Descripción', 'type' => 'textarea'],
                    ['name' => 'sort', 'label' => 'Orden', 'type' => 'number', 'default' => 0, 'table' => true],
                ],
            ],
            'proyectos' => [
                'label' => 'Proyectos', 'singular' => 'proyecto', 'model' => Models\Project::class,
                'search' => ['name'], 'order' => ['created_at', 'desc'], 'group' => 'Relación', 'filter' => 'institution_id',
                'fields' => [
                    ['name' => 'institution_id', 'label' => 'Institución', 'required' => true, 'table' => true] + $institution,
                    ['name' => 'business_unit_id', 'label' => 'Unidad', 'table' => true] + $unit,
                    ['name' => 'name', 'label' => 'Nombre', 'type' => 'text', 'required' => true, 'table' => true],
                    ['name' => 'description', 'label' => 'Descripción', 'type' => 'textarea'],
                    ['name' => 'status', 'label' => 'Estado', 'type' => 'select', 'required' => true, 'table' => true, 'default' => 'active',
                        'options' => ['active' => 'En ejecución', 'closed' => 'Cerrado']],
                    ['name' => 'progress', 'label' => 'Avance (%)', 'type' => 'number', 'min' => 0, 'max' => 100, 'default' => 0, 'table' => true],
                    ['name' => 'next_milestone', 'label' => 'Próximo hito', 'type' => 'text'],
                    ['name' => 'starts_on', 'label' => 'Inicio', 'type' => 'date'],
                    ['name' => 'ends_on', 'label' => 'Fin estimado', 'type' => 'date'],
                ],
            ],
            'fases' => [
                'label' => 'Fases de proyecto', 'singular' => 'fase', 'model' => Models\ProjectPhase::class,
                'search' => ['name'], 'order' => ['project_id', 'asc'], 'group' => 'Relación', 'filter' => 'project_id',
                'fields' => [
                    ['name' => 'project_id', 'label' => 'Proyecto', 'type' => 'belongsTo', 'model' => Models\Project::class,
                        'labelColumn' => 'name', 'required' => true, 'table' => true],
                    ['name' => 'position', 'label' => 'N.º de fase', 'type' => 'number', 'default' => 1, 'table' => true],
                    ['name' => 'name', 'label' => 'Nombre', 'type' => 'text', 'required' => true, 'table' => true],
                    ['name' => 'status', 'label' => 'Estado', 'type' => 'select', 'required' => true, 'table' => true, 'default' => 'pending',
                        'options' => ['done' => 'Completada', 'current' => 'En curso', 'pending' => 'Pendiente']],
                    ['name' => 'due_on', 'label' => 'Fecha objetivo', 'type' => 'date', 'table' => true],
                ],
            ],
            'bitacora' => [
                'label' => 'Bitácora de proyectos', 'singular' => 'entrada', 'model' => Models\ProjectUpdate::class,
                'search' => ['body'], 'order' => ['happened_on', 'desc'], 'group' => 'Relación', 'filter' => 'project_id',
                'fields' => [
                    ['name' => 'project_id', 'label' => 'Proyecto', 'type' => 'belongsTo', 'model' => Models\Project::class,
                        'labelColumn' => 'name', 'required' => true, 'table' => true],
                    ['name' => 'happened_on', 'label' => 'Fecha', 'type' => 'date', 'required' => true, 'table' => true],
                    ['name' => 'body', 'label' => 'Detalle', 'type' => 'textarea', 'required' => true, 'table' => true],
                ],
            ],
            'entregables' => [
                'label' => 'Entregables', 'singular' => 'entregable', 'model' => Models\Deliverable::class,
                'search' => ['title'], 'order' => ['created_at', 'desc'], 'group' => 'Relación', 'filter' => 'institution_id',
                'fields' => [
                    ['name' => 'institution_id', 'label' => 'Institución', 'required' => true, 'table' => true] + $institution,
                    ['name' => 'project_id', 'label' => 'Proyecto', 'type' => 'belongsTo', 'model' => Models\Project::class, 'labelColumn' => 'name'],
                    ['name' => 'business_unit_id', 'label' => 'Unidad'] + $unit,
                    ['name' => 'title', 'label' => 'Título', 'type' => 'text', 'required' => true, 'table' => true],
                    ['name' => 'kind', 'label' => 'Tipo', 'type' => 'select', 'options' => $kinds, 'required' => true, 'default' => 'informe', 'table' => true],
                    ['name' => 'version', 'label' => 'Versión', 'type' => 'text'],
                    ['name' => 'phase_label', 'label' => 'Fase (ej. F3)', 'type' => 'text'],
                    ['name' => 'file_path', 'label' => 'Archivo (PDF/DOCX/PPTX, máx. 10 MB)', 'type' => 'file',
                        'nameTo' => 'file_name', 'sizeTo' => 'file_size', 'hashTo' => 'sha256', 'folder' => 'entregables'],
                    ['name' => 'external_url', 'label' => 'O enlace externo (Drive)', 'type' => 'url',
                        'help' => 'Para archivos pesados. Calcule abajo la huella del archivo original para poder verificarlo.'],
                    ['name' => 'sha256', 'label' => 'Huella SHA-256', 'type' => 'hash',
                        'help' => 'Se calcula sola al subir un archivo. Para enlaces externos, use el botón para calcularla en su equipo.'],
                    ['name' => 'status', 'label' => 'Estado', 'type' => 'select', 'required' => true, 'table' => true, 'default' => 'published',
                        'options' => ['draft' => 'Borrador (oculto)', 'pending_approval' => 'Requiere aprobación del cliente',
                            'approved' => 'Aprobado', 'published' => 'Publicado']],
                    ['name' => 'published_at', 'label' => 'Fecha de publicación', 'type' => 'datetime'],
                ],
            ],
            'research' => [
                'label' => 'Reportes de Research', 'singular' => 'reporte', 'model' => Models\ResearchReport::class,
                'search' => ['title', 'author'], 'order' => ['published_on', 'desc'], 'group' => 'Contenido',
                'fields' => [
                    ['name' => 'title', 'label' => 'Título', 'type' => 'text', 'required' => true, 'table' => true],
                    ['name' => 'summary', 'label' => 'Resumen', 'type' => 'textarea'],
                    ['name' => 'topic', 'label' => 'Tema', 'type' => 'select', 'required' => true, 'table' => true,
                        'options' => array_combine(config('portal.research_topics'), config('portal.research_topics'))],
                    ['name' => 'author', 'label' => 'Autor/a', 'type' => 'text'],
                    ['name' => 'reading_minutes', 'label' => 'Minutos de lectura', 'type' => 'number'],
                    ['name' => 'institution_id', 'label' => 'Exclusivo para', 'table' => true,
                        'help' => 'Vacío = visible para todas las instituciones con Research.'] + $institution,
                    ['name' => 'file_path', 'label' => 'Archivo PDF (máx. 10 MB)', 'type' => 'file', 'nameTo' => 'file_name', 'folder' => 'research'],
                    ['name' => 'external_url', 'label' => 'O enlace externo (Drive, tablero)', 'type' => 'url'],
                    ['name' => 'is_featured', 'label' => 'Destacado', 'type' => 'boolean', 'table' => true],
                    ['name' => 'is_published', 'label' => 'Publicado', 'type' => 'boolean', 'default' => true, 'table' => true],
                    ['name' => 'published_on', 'label' => 'Fecha de publicación', 'type' => 'date', 'table' => true],
                ],
            ],
            'videos' => [
                'label' => 'Videos y webinars', 'singular' => 'video', 'model' => Models\Video::class,
                'search' => ['title', 'speaker'], 'order' => ['created_at', 'desc'], 'group' => 'Contenido',
                'fields' => [
                    ['name' => 'kind', 'label' => 'Formato', 'type' => 'select', 'required' => true, 'table' => true, 'default' => 'recorded',
                        'options' => ['live' => 'Webinar en vivo', 'recorded' => 'Grabado', 'pill' => 'Píldora']],
                    ['name' => 'title', 'label' => 'Título', 'type' => 'text', 'required' => true, 'table' => true],
                    ['name' => 'description', 'label' => 'Descripción', 'type' => 'textarea'],
                    ['name' => 'source_url', 'label' => 'Enlace del video (Drive, YouTube o Vimeo)', 'type' => 'video_url',
                        'help' => 'Pegue el enlace de compartir. En Drive: "Cualquier persona con el enlace". Para webinars en vivo puede quedar vacío hasta tener la grabación.'],
                    ['name' => 'topic', 'label' => 'Tema', 'type' => 'select', 'table' => true,
                        'options' => array_combine(config('portal.video_topics'), config('portal.video_topics'))],
                    ['name' => 'speaker', 'label' => 'Expositor/es', 'type' => 'text'],
                    ['name' => 'duration_minutes', 'label' => 'Duración (min)', 'type' => 'number'],
                    ['name' => 'scheduled_at', 'label' => 'Fecha y hora (en vivo)', 'type' => 'datetime', 'table' => true],
                    ['name' => 'live_url', 'label' => 'Enlace de la sala (Meet/Zoom/Teams)', 'type' => 'url'],
                    ['name' => 'thumbnail_url', 'label' => 'Miniatura (URL de imagen, opcional)', 'type' => 'url'],
                    ['name' => 'points', 'label' => 'Puntos de alianza al completarlo', 'type' => 'number', 'default' => 10],
                    ['name' => 'institution_id', 'label' => 'Exclusivo para', 'help' => 'Vacío = visible para todos.'] + $institution,
                    ['name' => 'is_published', 'label' => 'Publicado', 'type' => 'boolean', 'default' => true, 'table' => true],
                ],
            ],
            'facturas' => [
                'label' => 'Facturas', 'singular' => 'factura', 'model' => Models\Invoice::class,
                'search' => ['number', 'concept'], 'order' => ['issued_on', 'desc'], 'group' => 'Administración', 'filter' => 'institution_id',
                'fields' => [
                    ['name' => 'institution_id', 'label' => 'Institución', 'required' => true, 'table' => true] + $institution,
                    ['name' => 'number', 'label' => 'N.º de factura', 'type' => 'text', 'required' => true, 'table' => true],
                    ['name' => 'concept', 'label' => 'Concepto', 'type' => 'text', 'required' => true, 'table' => true],
                    ['name' => 'issued_on', 'label' => 'Emisión', 'type' => 'date', 'required' => true, 'table' => true],
                    ['name' => 'due_on', 'label' => 'Vencimiento', 'type' => 'date'],
                    ['name' => 'amount', 'label' => 'Monto', 'type' => 'decimal', 'required' => true, 'table' => true],
                    ['name' => 'currency', 'label' => 'Moneda', 'type' => 'select', 'options' => ['BOB' => 'Bolivianos', 'USD' => 'Dólares'], 'default' => 'BOB'],
                    ['name' => 'status', 'label' => 'Estado', 'type' => 'select', 'required' => true, 'table' => true, 'default' => 'pending',
                        'options' => ['pending' => 'Pendiente', 'paid' => 'Pagada', 'void' => 'Anulada']],
                    ['name' => 'paid_on', 'label' => 'Fecha de pago', 'type' => 'date'],
                    ['name' => 'pdf_path', 'label' => 'PDF de la factura', 'type' => 'file', 'folder' => 'facturas', 'accept' => '.pdf'],
                    ['name' => 'xml_path', 'label' => 'XML (SIAT)', 'type' => 'file', 'folder' => 'facturas', 'accept' => '.xml'],
                ],
            ],
            'contratos' => [
                'label' => 'Contratos y presupuestos', 'singular' => 'contrato', 'model' => Models\Contract::class,
                'search' => ['title'], 'order' => ['signed_on', 'desc'], 'group' => 'Administración', 'filter' => 'institution_id',
                'fields' => [
                    ['name' => 'institution_id', 'label' => 'Institución', 'required' => true, 'table' => true] + $institution,
                    ['name' => 'business_unit_id', 'label' => 'Unidad', 'table' => true] + $unit,
                    ['name' => 'kind', 'label' => 'Tipo', 'type' => 'select', 'required' => true, 'default' => 'contract', 'table' => true,
                        'options' => ['contract' => 'Contrato', 'quote' => 'Presupuesto firmado']],
                    ['name' => 'title', 'label' => 'Título', 'type' => 'text', 'required' => true, 'table' => true],
                    ['name' => 'signed_on', 'label' => 'Fecha de firma', 'type' => 'date', 'table' => true],
                    ['name' => 'expires_on', 'label' => 'Vigencia hasta', 'type' => 'date'],
                    ['name' => 'amount', 'label' => 'Monto', 'type' => 'decimal'],
                    ['name' => 'file_path', 'label' => 'Archivo (PDF)', 'type' => 'file', 'nameTo' => 'file_name', 'hashTo' => 'sha256', 'folder' => 'contratos'],
                    ['name' => 'external_url', 'label' => 'O enlace externo', 'type' => 'url'],
                    ['name' => 'sha256', 'label' => 'Huella SHA-256', 'type' => 'hash'],
                ],
            ],
            'horas' => [
                'label' => 'Movimientos de horas', 'singular' => 'movimiento', 'model' => Models\HourMovement::class,
                'search' => ['description'], 'order' => ['created_at', 'desc'], 'group' => 'Administración', 'filter' => 'institution_id',
                'fields' => [
                    ['name' => 'institution_id', 'label' => 'Institución', 'required' => true, 'table' => true] + $institution,
                    ['name' => 'hours', 'label' => 'Horas (+ abono / − consumo)', 'type' => 'decimal', 'required' => true, 'table' => true],
                    ['name' => 'description', 'label' => 'Detalle', 'type' => 'text', 'required' => true, 'table' => true],
                    ['name' => 'expires_on', 'label' => 'Vencen el (solo abonos)', 'type' => 'date', 'table' => true],
                ],
            ],
        ];
    }

    public static function get(string $key): array
    {
        $all = static::all();
        abort_unless(isset($all[$key]), 404);

        return $all[$key] + ['key' => $key];
    }

    /** Proveedores aceptados para el campo video_url (para mostrar ayuda en el formulario) */
    public static function videoProviders(): array
    {
        return VideoEmbed::PROVIDERS;
    }
}
