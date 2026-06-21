<?php

return [
    'costo_envio' => [
        /** Tarifa fija de arranque (referencia delivery urbano Bolivia). */
        'tarifa_base_bs' => (float) env('LOGISTICA_COSTO_BASE_BS', 10),
        /** Bs por kilómetro recorrido. */
        'por_km_bs' => (float) env('LOGISTICA_COSTO_POR_KM_BS', 2.4),
        /** Recargo por cada parada adicional (recogida intermedia). */
        'por_parada_extra_bs' => (float) env('LOGISTICA_COSTO_PARADA_EXTRA_BS', 5),
        /** Mínimo cobrable por ruta. */
        'minimo_bs' => (float) env('LOGISTICA_COSTO_MINIMO_BS', 15),
        /** Recargo porcentual si hay lluvia (resto de climas: normal). */
        'recargo_lluvia_pct' => (float) env('LOGISTICA_RECARGO_LLUVIA_PCT', 25),
    ],
];
