<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Blockchain BaaS (api-middleware AgroFusion)
    |--------------------------------------------------------------------------
    |
    | En Railway: LEDGER_MODE=cloud sobre Postgres + hash-chain.
    | En local puede apuntar a http://127.0.0.1:3000 con Fabric real.
    |
    */

    'enabled' => (bool) env('BLOCKCHAIN_ENABLED', false),

    'api_url' => rtrim((string) env('BLOCKCHAIN_API_URL', 'http://127.0.0.1:3000'), '/'),

    'api_key' => (string) env('BLOCKCHAIN_API_KEY', ''),

    'timeout' => (int) env('BLOCKCHAIN_TIMEOUT', 20),

    /** Máximo de reintentos automáticos desde el comando de sincronización. */
    'max_intentos' => (int) env('BLOCKCHAIN_MAX_INTENTOS', 8),

];
