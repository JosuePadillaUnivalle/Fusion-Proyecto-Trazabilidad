<?php

return [
    'campos' => [
        'nombre' => 'Nombre único del almacén. Lo verá en movimientos, inventario y paneles por rol.',
        'descripcion' => 'Breve detalle del uso: producto terminado, materia prima, cuarentena, etc.',
        'ubicacion' => 'Dirección física. Use "Buscar en otra pantalla" para filtrar ubicaciones existentes o escriba una nueva si no aparece.',
        'capacidad' => 'Volumen máximo que puede almacenar este depósito. Use números decimales si aplica (ej. 1500,5).',
        'unidadmedidaid' => 'Unidad en la que expresa la capacidad: kilogramos, toneladas, metros cúbicos, etc.',
        'tipoalmacenid' => 'Clasificación operativa: central recibe de varios puntos; planta enlaza con producción; secundario es satélite.',
        'activo' => 'Si está desactivado, no aparecerá en listas de operación diaria, pero conserva su historial.',
    ],

    'tipos' => [
        'Central' => 'Almacén principal de distribución y consolidación de stock.',
        'Secundario' => 'Punto satélite o regional con menor capacidad que el central.',
        'Planta' => 'Ubicado en planta de procesamiento; recibe producción y despacha a otros almacenes.',
    ],

    'ubicaciones_sugeridas' => [
        'Parque Industrial, Santa Cruz de la Sierra',
        'Zona Norte, Santa Cruz de la Sierra',
        'Zona industrial demo, Santa Cruz',
        'Área de cuarentena',
        'Cámara frigorífica',
    ],
];
