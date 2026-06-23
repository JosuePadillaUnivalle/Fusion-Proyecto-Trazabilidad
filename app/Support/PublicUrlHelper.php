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
     * Usa APP_PUBLIC_URL (IP LAN) aunque el usuario navegue en 127.0.0.1 en el PC.
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

        if ($preferPublic && $publicUrl !== '') {
            return rtrim($publicUrl, '/');
        }

        if ($publicUrl !== '') {
            return rtrim($publicUrl, '/');
        }

        return rtrim((string) config('app.url', 'http://localhost'), '/');
    }
}
