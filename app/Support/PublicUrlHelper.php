<?php

namespace App\Support;

final class PublicUrlHelper
{
    public static function absolute(string $path = ''): string
    {
        $path = '/'.ltrim($path, '/');

        return rtrim(self::baseUrl(), '/').($path === '/' ? '' : $path);
    }

    public static function baseUrl(): string
    {
        $publicUrl = trim((string) config('app.public_url', ''));
        if ($publicUrl !== '') {
            return rtrim($publicUrl, '/');
        }

        if (! app()->runningInConsole() && request()) {
            return self::construirDesdeRequest(request());
        }

        $appUrl = (string) config('app.url', 'http://localhost');
        $partes = parse_url($appUrl) ?: [];
        $scheme = $partes['scheme'] ?? 'http';
        $host = $partes['host'] ?? 'localhost';
        $port = isset($partes['port']) ? (int) $partes['port'] : null;

        if (self::esLoopback($host)) {
            $lan = self::ipRedLocal();
            if ($lan !== null) {
                $host = $lan;
            }
        }

        return self::ensamblar($scheme, $host, $port);
    }

    /** @param  \Illuminate\Http\Request  $request */
    private static function construirDesdeRequest($request): string
    {
        $scheme = $request->getScheme();
        $host = $request->getHost();
        $port = (int) $request->getPort();

        if (self::esLoopback($host)) {
            $lan = self::ipRedLocal();
            if ($lan !== null) {
                $host = $lan;
            }
        }

        return self::ensamblar($scheme, $host, $port ?: null);
    }

    private static function ensamblar(string $scheme, string $host, ?int $port): string
    {
        $puerto = self::sufijoPuerto($port, $scheme);

        return "{$scheme}://{$host}{$puerto}";
    }

    private static function sufijoPuerto(?int $port, string $scheme): string
    {
        if ($port === null || $port <= 0) {
            return '';
        }

        $defecto = $scheme === 'https' ? 443 : 80;

        return $port === $defecto ? '' : ":{$port}";
    }

    private static function esLoopback(string $host): bool
    {
        return in_array(strtolower($host), ['localhost', '127.0.0.1', '::1'], true);
    }

    private static function ipRedLocal(): ?string
    {
        if (function_exists('socket_create')) {
            $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            if ($sock !== false) {
                @socket_connect($sock, '10.255.255.255', 1);
                @socket_getsockname($sock, $addr);
                @socket_close($sock);

                if (is_string($addr) && $addr !== '' && ! self::esLoopback($addr)) {
                    return $addr;
                }
            }
        }

        $hostname = gethostname();
        if (! is_string($hostname) || $hostname === '') {
            return null;
        }

        $ip = gethostbyname($hostname);

        if (is_string($ip) && $ip !== $hostname && ! self::esLoopback($ip)) {
            return $ip;
        }

        return null;
    }
}
