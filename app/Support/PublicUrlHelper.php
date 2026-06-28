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
     * URL absoluta para QR / acceso desde móvil.
     * En Railway usa el dominio público (https). En local, IP LAN si abre 127.0.0.1.
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
        if ($remota = self::resolveUrlDespliegueRemoto()) {
            return $remota;
        }

        if ($preferPublic && request()) {
            $host = strtolower(request()->getHost());
            $port = (int) request()->getPort();

            if (self::esLoopback($host)) {
                $lanUrl = LanNetworkResolver::resolvePublicUrl($port > 0 ? $port : null);
                if ($lanUrl !== null) {
                    return rtrim($lanUrl, '/');
                }
            }
        }

        $publicUrl = trim((string) config('app.public_url', ''));

        if ($publicUrl !== '' && ! ($preferPublic && self::esDespliegueRemoto() && self::esUrlPrivada($publicUrl))) {
            return rtrim($publicUrl, '/');
        }

        if (! app()->runningInConsole() && request()) {
            $host = strtolower(request()->getHost());
            if (! self::esUrlPrivadaHost($host)) {
                return rtrim(request()->getSchemeAndHttpHost(), '/');
            }
        }

        $lanUrl = LanNetworkResolver::resolvePublicUrl(
            (int) (env('SERVER_PORT') ?: 8001) ?: null
        );
        if ($preferPublic && $lanUrl !== null) {
            return rtrim($lanUrl, '/');
        }

        $appUrl = trim((string) config('app.url', 'http://localhost'));

        return rtrim($appUrl !== '' && ! self::esUrlPrivada($appUrl) ? $appUrl : 'http://localhost', '/');
    }

    /** URL pública en Railway / hosting (nunca IP local). */
    private static function resolveUrlDespliegueRemoto(): ?string
    {
        if (! self::esDespliegueRemoto()) {
            return null;
        }

        foreach ([
            getenv('RAILWAY_PUBLIC_DOMAIN') ?: null,
            getenv('RAILWAY_STATIC_URL') ?: null,
            config('app.url'),
        ] as $candidato) {
            $url = self::normalizarUrlPublica($candidato);
            if ($url !== null) {
                return $url;
            }
        }

        if (request()) {
            $host = strtolower(request()->getHost());
            if (! self::esUrlPrivadaHost($host)) {
                $scheme = request()->getScheme() === 'http' ? 'https' : request()->getScheme();

                return rtrim($scheme.'://'.$host.(
                    request()->getPort() && ! in_array((int) request()->getPort(), [80, 443], true)
                        ? ':'.request()->getPort()
                        : ''
                ), '/');
            }
        }

        return null;
    }

    private static function esDespliegueRemoto(): bool
    {
        return (bool) (getenv('RAILWAY_ENVIRONMENT') ?: getenv('RAILWAY_PROJECT_ID'));
    }

    private static function normalizarUrlPublica(mixed $valor): ?string
    {
        $valor = trim((string) $valor);
        if ($valor === '') {
            return null;
        }

        if (! str_contains($valor, '://')) {
            $valor = 'https://'.ltrim($valor, '/');
        }

        $host = parse_url($valor, PHP_URL_HOST);
        if (! is_string($host) || self::esUrlPrivadaHost(strtolower($host))) {
            return null;
        }

        $scheme = parse_url($valor, PHP_URL_SCHEME);

        return rtrim(($scheme === 'http' ? 'https' : ($scheme ?: 'https')).'://'.$host, '/');
    }

    private static function esUrlPrivada(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && self::esUrlPrivadaHost(strtolower($host));
    }

    private static function esUrlPrivadaHost(string $host): bool
    {
        if (self::esLoopback($host)) {
            return true;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
        }

        return false;
    }

    private static function esLoopback(string $host): bool
    {
        return in_array($host, ['127.0.0.1', 'localhost', '::1'], true);
    }
}
