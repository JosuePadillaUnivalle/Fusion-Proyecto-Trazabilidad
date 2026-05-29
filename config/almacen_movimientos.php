<?php

return [
    'campos' => [
        'almacen' => 'Ubicación física donde se registra el movimiento. Solo verá insumos asociados a ese almacén.',
        'insumo' => 'Producto o insumo que entra o sale. El stock se actualiza automáticamente al guardar.',
        'tipo' => 'Motivo del movimiento. Elija el que mejor describa la operación (ver descripción al seleccionar).',
        'fecha' => 'Día en que ocurrió el hecho en almacén (puede ser hoy o una fecha anterior).',
        'cantidad' => 'Unidades a registrar, en la medida del insumo (kg, litros, qq, und, etc.). Mínimo 0,001.',
        'referencia' => 'Pulse la lupa para abrir el buscador (pestaña Referencia): filtre por código, envío o pedido.',
        'destino_motivo' => 'Pulse la lupa para abrir el buscador (pestaña Destino / motivo) o complételo al elegir una referencia.',
        'observaciones' => 'Notas libres: responsable del envío, temperatura, incidencias, acuerdos con proveedor, etc.',
    ],

    'tipos' => [
        'ingreso' => [
            'Compra' => 'Mercancía adquirida a un proveedor. Aumenta el stock por compra facturada o recepción.',
            'Producción recibida' => 'Producto terminado o cosecha que llega desde planta o campo al almacén.',
            'Devolución' => 'Material que regresa al almacén (cliente, área interna o envío no entregado).',
            'Ajuste positivo' => 'Corrección manual al alza: conteo físico mayor al sistema, regularización de inventario.',
        ],
        'salida' => [
            'Envío' => 'Mercancía que sale hacia otro almacén, cliente o punto de distribución.',
            'Consumo interno' => 'Uso dentro de la empresa (producción, mantenimiento, muestras, personal).',
            'Merma' => 'Pérdida por vencimiento, daño, humedad o calidad no apta para venta.',
            'Ajuste negativo' => 'Corrección manual a la baja: conteo físico menor al sistema o faltante detectado.',
            'Despacho' => 'Salida para entrega o despacho logístico (similar a envío, según su operación).',
        ],
    ],

    'destinos_ingreso' => [
        'Proveedor externo',
        'Planta procesadora',
        'Campo / parcela productiva',
        'Devolución de cliente',
        'Área de cuarentena',
    ],

    'destinos_salida' => [
        'Área de producción',
        'Cocina / comedor interno',
        'Mantenimiento y operaciones',
        'Muestras de calidad',
        'Baja por merma',
        'Cliente mayorista',
        'Punto de venta',
    ],

    'destinos_por_tipo' => [
        'ingreso' => [
            'Compra' => ['Proveedor: {proveedor}', 'Recepción en {almacen}'],
            'Producción recibida' => ['Planta procesadora', 'Campo / cosecha → {almacen}'],
            'Devolución' => ['Devolución de cliente', 'Recepción en {almacen}'],
            'Ajuste positivo' => ['Regularización de inventario en {almacen}'],
        ],
        'salida' => [
            'Envío' => ['Despacho desde {almacen}', 'Cliente / punto de entrega'],
            'Consumo interno' => ['Área de producción', 'Cocina / comedor interno'],
            'Merma' => ['Baja por merma', 'Descarte en {almacen}'],
            'Ajuste negativo' => ['Regularización de inventario en {almacen}'],
            'Despacho' => ['Despacho logístico desde {almacen}'],
        ],
    ],
];
