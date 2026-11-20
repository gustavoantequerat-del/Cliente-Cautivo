<?php

// Mensajes de validación en español (sin esto Laravel muestra claves como "validation.required")
return [
    'array' => 'El campo :attribute no es válido.',
    'boolean' => 'El campo :attribute debe ser sí o no.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'current_password' => 'La contraseña no es correcta.',
    'date' => 'El campo :attribute no es una fecha válida.',
    'email' => 'Escriba un correo válido.',
    'exists' => 'El valor elegido en :attribute no es válido.',
    'file' => 'El campo :attribute debe ser un archivo.',
    'in' => 'El valor elegido en :attribute no es válido.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'max' => [
        'numeric' => 'El campo :attribute no puede ser mayor que :max.',
        'file' => 'El archivo no puede pesar más de :max KB.',
        'string' => 'El campo :attribute no puede tener más de :max caracteres.',
        'array' => 'El campo :attribute no puede tener más de :max elementos.',
    ],
    'mimes' => 'El archivo debe ser de tipo: :values.',
    'min' => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'file' => 'El archivo debe pesar al menos :min KB.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
        'array' => 'El campo :attribute debe tener al menos :min elementos.',
    ],
    'numeric' => 'El campo :attribute debe ser un número.',
    'password' => [
        'letters' => 'La contraseña debe tener al menos una letra.',
        'mixed' => 'La contraseña debe tener mayúsculas y minúsculas.',
        'numbers' => 'La contraseña debe tener al menos un número.',
        'symbols' => 'La contraseña debe tener al menos un símbolo.',
        'uncompromised' => 'Esta contraseña apareció en una filtración. Elija otra.',
    ],
    'regex' => 'El formato de :attribute no es válido.',
    'required' => 'Complete el campo :attribute.',
    'required_if' => 'Complete el campo :attribute.',
    'string' => 'El campo :attribute debe ser texto.',
    'unique' => 'Ya existe un registro con ese :attribute.',
    'url' => 'Escriba un enlace válido (empieza con https://).',

    // Nombres legibles de los campos (columnas de la base de datos)
    'attributes' => [
        'nombre' => 'nombre', 'email' => 'correo', 'password' => 'contraseña', 'current_password' => 'contraseña actual',
        'rol' => 'rol', 'institucion_id' => 'institución', 'telefono' => 'teléfono', 'nit' => 'NIT', 'identificador' => 'identificador',
        'codigo' => 'código', 'codigo_verificacion' => 'código de verificación', 'puntos_alianza' => 'puntos de alianza',
        'horas_estimadas' => 'horas estimadas', 'horas_cobradas' => 'horas a descontar', 'descripcion' => 'descripción',
        'especialidad' => 'especialidad', 'modalidad' => 'tipo de solicitud', 'titulo' => 'título', 'enlace_original' => 'enlace del video',
        'enlace_externo' => 'enlace externo', 'monto' => 'monto', 'numero' => 'número', 'concepto' => 'concepto', 'estado' => 'estado',
        'usuario_id' => 'persona', 'proyecto_id' => 'proyecto', 'unidad_negocio_id' => 'unidad',
    ],
];
