<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    
    
    //OrgTrack - Sistema de Envíos Externo
    
    'orgtrack' => [
        'url' => env('ORGTRACK_API_URL', 'http://127.0.0.1:8001/api'),
        'timeout' => env('ORGTRACK_TIMEOUT', 10),
        'token' => env('ORGTRACK_API_TOKEN'),
        /** Si la API responde OK pero sin lista, usar datos locales (demo). Desactivar en producción con OrgTrack real si devuelve arrays vacíos válidos. */
        'local_fallback_when_empty' => env('ORGTRACK_LOCAL_FALLBACK_WHEN_EMPTY', true),
    ],

    'weather' => [
        'key' => env('WEATHER_API_KEY', env('OPENWEATHER_API_KEY')),
        'city' => env('WEATHER_CITY', 'Santa Cruz de la Sierra'),
        'country' => env('WEATHER_COUNTRY', 'BO'),
        'units' => env('WEATHER_UNITS', 'metric'),
        'cache_ttl' => (int) env('WEATHER_CACHE_TTL', 1200),
    ],

    // Alias para compatibilidad con código legado que usa services.openweather.key
    'openweather' => [
        'key' => env('OPENWEATHER_API_KEY', env('WEATHER_API_KEY')),
    ],

];