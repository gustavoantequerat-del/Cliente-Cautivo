<?php

return [
    // Niveles de alianza: dependen de cuántos servicios (unidades de negocio) contrató la institución.
    // Servicios_Minimos null = todos los servicios.
    'Niveles_Alianza' => [
        ['Clave' => 'explorador', 'Nombre' => 'Explorador', 'Servicios_Minimos' => 1,
            'Beneficios' => 'Acceso al portal, biblioteca de Research y agenda de eventos.'],
        ['Clave' => 'aliado', 'Nombre' => 'Aliado', 'Servicios_Minimos' => 2,
            'Beneficios' => 'Reportes completos, Laboratorio de herramientas y certificados verificables.'],
        ['Clave' => 'socio_estrategico', 'Nombre' => 'Socio Estratégico', 'Servicios_Minimos' => null,
            'Beneficios' => 'Todos los beneficios: análisis a medida, cupos NEXUM de cortesía, Consejo Consultivo y acceso anticipado a productos.'],
    ],

    // Tamaño máximo de subida en KB (los videos nunca se suben: van por enlace)
    'Tamano_Maximo_Subida_Kb' => env('PORTAL_MAX_UPLOAD_KB', 10240),

    // Correo al que llegan las solicitudes de horas
    'Correo_Notificaciones' => env('PORTAL_NOTIFY_EMAIL'),
];
