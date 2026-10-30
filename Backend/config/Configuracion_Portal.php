<?php

return [
    // Niveles de alianza: puntos mínimos y beneficios (editable sin tocar la base de datos)
    'Niveles_Alianza' => [
        ['Clave' => 'explorador', 'Nombre' => 'Explorador', 'Puntos_Minimos' => 0,
            'Beneficios' => 'Acceso al portal, biblioteca básica de Research y agenda de eventos.'],
        ['Clave' => 'aliado', 'Nombre' => 'Aliado', 'Puntos_Minimos' => 250,
            'Beneficios' => 'Reportes completos, Laboratorio de herramientas y certificados verificables.'],
        ['Clave' => 'socio_estrategico', 'Nombre' => 'Socio Estratégico', 'Puntos_Minimos' => 500,
            'Beneficios' => 'Análisis a medida, cupos NEXUM de cortesía y mesas regulatorias con socios.'],
        ['Clave' => 'socio_fundador', 'Nombre' => 'Socio Fundador', 'Puntos_Minimos' => 1000,
            'Beneficios' => 'Asiento en el Consejo Consultivo, co-autoría de reportes y acceso anticipado a productos.'],
    ],

    // Tamaño máximo de subida en KB (los videos nunca se suben: van por enlace)
    'Tamano_Maximo_Subida_Kb' => env('PORTAL_MAX_UPLOAD_KB', 10240),

    // Correo al que llegan las solicitudes de horas
    'Correo_Notificaciones' => env('PORTAL_NOTIFY_EMAIL'),
];
