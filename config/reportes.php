<?php



return [



    /*

    |--------------------------------------------------------------------------

    | Catálogo del Centro de Reportes AgroFusion

    |--------------------------------------------------------------------------

    | permission: permiso Spatie requerido para ver el reporte

    | roles: roles que pueden verlo (además de admin global)

    | service: método del contenedor ReporteCentroService

    | vista: blade bajo resources/views/reportes/

    | filtros: claves definidas en ReporteFiltrosCatalogo

    */



    'items' => [

        [

            'slug' => 'envios-estado',

            'route' => 'reportes.envios-estado',

            'permission' => 'reportes.view',

            'title' => 'Envíos por estado',

            'subtitle' => 'Agrícola, rutas logísticas y pedidos PDV',

            'icon' => 'fa-chart-pie',

            'accent' => 'forest',

            'vista' => 'envios-estado',

            'preview' => 'enviosEstadoPreview',

            'filtros' => ['periodo', 'estado_envio'],

        ],

        [

            'slug' => 'stock-ambito',

            'route' => 'reportes.stock-ambito',

            'title' => 'Stock por ámbito',

            'subtitle' => 'Agrícola, planta, mayorista y puntos de venta',

            'icon' => 'fa-warehouse',

            'accent' => 'teal',

            'vista' => 'stock-ambito',

            'preview' => 'stockAmbitoPreview',

            'filtros' => ['ambito', 'solo_criticos'],

        ],

        [

            'slug' => 'transportistas',

            'route' => 'reportes.transportistas',

            'title' => 'Rendimiento transportistas',

            'subtitle' => 'Asignaciones y rutas por chofer',

            'icon' => 'fa-truck',

            'accent' => 'navy',

            'vista' => 'transportistas',

            'preview' => 'transportistasPreview',

            'filtros' => ['periodo', 'transportista'],

        ],

        [

            'slug' => 'traslados-planta-mayorista',

            'route' => 'reportes.traslados-planta-mayorista',

            'title' => 'Traslados planta → mayorista',

            'subtitle' => 'Estado y volumen de envíos a mayorista',

            'icon' => 'fa-dolly',

            'accent' => 'bronze',

            'vista' => 'traslados-planta-mayorista',

            'preview' => 'trasladosPreview',

            'filtros' => ['periodo', 'estado_ruta', 'almacen_planta', 'almacen_destino'],

        ],

        [

            'slug' => 'pedidos-pdv',

            'route' => 'reportes.pedidos-pdv',

            'title' => 'Pedidos a puntos de venta',

            'subtitle' => 'Solicitudes minoristas y recepciones',

            'icon' => 'fa-store',

            'accent' => 'wine',

            'vista' => 'pedidos-pdv',

            'preview' => 'pedidosPdvPreview',

            'filtros' => ['periodo', 'estado_pdv', 'punto_venta'],

        ],

        [

            'slug' => 'productos-terminados',

            'route' => 'reportes.productos-terminados',

            'title' => 'Productos terminados',

            'subtitle' => 'Catálogo y stock en planta y mayorista',

            'icon' => 'fa-box-open',

            'accent' => 'indigo',

            'vista' => 'productos-terminados',

            'preview' => 'productosTerminadosPreview',

            'filtros' => ['ambito_pt', 'busqueda'],

        ],

    ],



];


