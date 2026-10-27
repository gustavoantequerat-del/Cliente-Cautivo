<?php

return [
    // Niveles de alianza: puntos mínimos y beneficios (editable sin tocar BD)
    'levels' => [
        ['key' => 'explorador', 'name' => 'Explorador', 'points' => 0,
            'benefits' => 'Acceso al portal, biblioteca básica de Research y agenda de eventos.'],
        ['key' => 'aliado', 'name' => 'Aliado', 'points' => 250,
            'benefits' => 'Reportes completos, Laboratorio de herramientas y certificados verificables.'],
        ['key' => 'socio_estrategico', 'name' => 'Socio Estratégico', 'points' => 500,
            'benefits' => 'Análisis a medida, cupos NEXUM de cortesía y mesas regulatorias con socios.'],
        ['key' => 'socio_fundador', 'name' => 'Socio Fundador', 'points' => 1000,
            'benefits' => 'Asiento en el Consejo Consultivo, co-autoría de reportes y acceso anticipado a productos.'],
    ],

    'research_topics' => ['Mercado P2P', 'Regulación', 'Adopción institucional', 'Stablecoins', 'Tokenización'],

    'video_topics' => ['Normativa', 'Metodologías', 'Herramientas'],

    'deliverable_kinds' => [
        'informe' => 'Informe', 'presentacion' => 'Presentación', 'auditoria' => 'Auditoría',
        'plantilla' => 'Plantilla', 'otro' => 'Otro',
    ],

    'specialties' => ['Cumplimiento y antilavado', 'Regulación internacional', 'Tecnología y custodia', 'Mercado y datos'],

    // Tamaño máximo de subida en KB (los videos nunca se suben: van por enlace)
    'max_upload_kb' => env('PORTAL_MAX_UPLOAD_KB', 10240),

    // Correo al que llegan las solicitudes de horas
    'notify_email' => env('PORTAL_NOTIFY_EMAIL'),
];
