<?php

return [
    // Niveles de alianza: dependen de cuántos productos o servicios (unidades de negocio) tiene activos la institución.
    // Color = color de la credencial del nivel.
    'Niveles_Alianza' => [
        ['Clave' => 'explorador', 'Nombre' => 'Cliente Explorador', 'Servicios_Minimos' => 1, 'Color' => '#8A939E',
            'Beneficios' => ['Acceso a webinars abiertos', 'Prueba guiada de una herramienta', 'Soporte local en 24 horas']],
        ['Clave' => 'aliado', 'Nombre' => 'Aliado Institucional', 'Servicios_Minimos' => 2, 'Color' => '#2F6FD6',
            'Beneficios' => ['Webinar trimestral exclusivo', '3 cupos para el Foro', 'Ejecutiva de cuenta asignada']],
        ['Clave' => 'premium', 'Nombre' => 'Premium', 'Servicios_Minimos' => 3, 'Color' => '#16294A',
            'Beneficios' => ['Research a medida y benchmark', 'Mesa cerrada con reguladores', 'Línea prioritaria y comité trimestral']],
    ],

    // Tamaño máximo de subida en KB (los videos nunca se suben: van por enlace)
    'Tamano_Maximo_Subida_Kb' => env('PORTAL_MAX_UPLOAD_KB', 20480),

    // Correo al que llegan las solicitudes de horas
    'Correo_Notificaciones' => env('PORTAL_NOTIFY_EMAIL'),
];
