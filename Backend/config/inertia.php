<?php

return [
    // Las pruebas comprueban que cada página que pide el Backend exista en el Frontend
    'testing' => [
        'ensure_pages_exist' => true,
        'page_paths' => [base_path('../Frontend/Paginas')],
        'page_extensions' => ['vue'],
    ],
];
