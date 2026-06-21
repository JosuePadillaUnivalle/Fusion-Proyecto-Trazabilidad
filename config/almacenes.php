<?php

return [
    'campos' => [
        'nombre' => 'Nombre único del depósito. Aparece en movimientos, cosechas y reportes del ámbito seleccionado.',
        'descripcion' => 'Opcional. Detalle de uso: frío, materia prima, cuarentena, etc.',
        'ubicacion' => 'Dirección o referencia física. En almacén agrícola puede marcar el punto exacto en el mapa.',
        'capacidad' => 'Capacidad máxima en kilogramos (kg). El sistema calcula cuánto espacio queda al registrar cosechas.',
    ],
    'campos_por_ambito' => [
        'mayorista' => [
            'nombre' => 'Nombre único del depósito. Aparece en movimientos y reportes del almacén mayorista.',
            'descripcion' => 'Opcional. Detalle de uso: distribución regional, cámara fría, etc.',
            'ubicacion' => 'Dirección o referencia física del punto de distribución mayorista.',
            'capacidad' => 'Capacidad máxima en kilogramos (kg). El sistema calcula cuánto espacio queda al registrar productos terminados provenientes de planta.',
        ],
        'planta' => [
            'capacidad' => 'Capacidad máxima en kilogramos (kg). El sistema calcula cuánto espacio queda al registrar productos terminados del procesamiento.',
        ],
    ],
];
