<?php

namespace App\Support;

final class PublicUrlHelper
{
    public static function absolute(string $path = ''): string
    {
        $path = '/'.ltrim($path, '/');
        $suffix = $path === '/' ? '' : $path;

        // En peticiones web, el QR debe usar el mismo host/puerto que el usuario ya tiene abierto
        // (127.0.0.1, localhost o la IP LAN actual). Evita enlaces a APP_PUBLIC_URL obsoletos.
        if (! app()->runningInConsole() && request()) {
            return rtrim(request()->getSchemeAndHttpHost(), '/').$suffix;
        }

        $publicUrl = trim((string) config('app.public_url', ''));
        if ($publicUrl !== '') {
            return rtrim($publicUrl, '/').$suffix;
        }

        return rtrim((string) config('app.url', 'http://localhost'), '/').$suffix;
    }

    public static function baseUrl(): string
    {
        return self::absolute('/');
    }
}
