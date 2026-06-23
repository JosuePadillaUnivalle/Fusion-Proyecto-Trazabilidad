<?php

namespace App\Support;

final class PublicUrlHelper
{
    public static function absolute(string $path = ''): string
    {
        $path = '/'.ltrim($path, '/');
        $suffix = $path === '/' ? '' : $path;

        if (! app()->runningInConsole() && request()) {
            return rtrim(request()->getSchemeAndHttpHost(), '/').$suffix;
        }

        return self::resolveBaseUrl().$suffix;
    }

    /**
     * URL absoluta para QR / celular en la misma WiFi.
     * Usa APP_PUBLIC_URL o detecta la IP LAN aunque el PC use 127.0.0.1 en el navegador.
     */
    public static function absoluteForQr(string $path = ''): string
    {
        $path = '/'.ltrim($path, '/');
        $suffix = $path === '/' ? '' : $path;

        return self::resolveBaseUrl(preferPublic: true).$suffix;
    }

    public static function baseUrl(): string
    {
        return self::absolute('/');
    }

    private static function resolveBaseUrl(bool $preferPublic = false): string
    {
        $publicUrl = trim((string) config('app.public_url', ''));

        if ($publicUrl !== '') {
            return rtrim($publicUrl, '/');
        }

        if ($preferPublic && ! app()->runningInConsole() && request()) {
            $host = strtolower(request()->getHost());
            $port = (int) request()->getPort();

            if (self::esLoopback($host)) {
                $lanUrl = LanNetworkResolver::resolvePublicUrl($port > 0 ? $port : null);
                if ($lanUrl !== null) {
                    return rtrim($lanUrl, '/');
                }
            }
        }

        if (! app()->runningInConsole() && request()) {
            return rtrim(request()->getSchemeAndHttpHost(), '/');
        }

        $lanUrl = LanNetworkResolver::resolvePublicUrl(
            (int) (env('SERVER_PORT') ?: 8001) ?: null
        );
        if ($preferPublic && $lanUrl !== null) {
            return rtrim($lanUrl, '/');
        }

        return rtrim((string) config('app.url', 'http://localhost'), '/');
    }

    private static function esLoopback(string $host): bool
    {
        return in_array($host, ['127.0.0.1', 'localhost', '::1'], true);
    }
}
