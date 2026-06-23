<?php

namespace App\Support;

final class LanNetworkResolver
{
    public static function detectIpv4(): ?string
    {
        if (function_exists('socket_create')) {
            $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            if ($sock !== false) {
                @socket_connect($sock, '8.8.8.8', 53);
                @socket_getsockname($sock, $address);
                @socket_close($sock);

                if (! empty($address) && $address !== '127.0.0.1') {
                    return $address;
                }
            }
        }

        $connection = @fsockopen('8.8.8.8', 53, $errno, $errstr, 1);
        if ($connection !== false) {
            $localAddress = stream_socket_get_name($connection, false);
            fclose($connection);

            if (is_string($localAddress) && str_contains($localAddress, ':')) {
                [$ip] = explode(':', $localAddress);
                if ($ip !== '' && $ip !== '127.0.0.1') {
                    return $ip;
                }
            }
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('ipconfig');
            if (is_string($output) && preg_match_all('/IPv4[^\:]*:\s*(\d+\.\d+\.\d+\.\d+)/', $output, $matches)) {
                foreach ($matches[1] as $ip) {
                    if ($ip !== '127.0.0.1' && ! str_starts_with($ip, '169.254.')) {
                        return $ip;
                    }
                }
            }
        }

        return null;
    }

    public static function resolvePublicUrl(?int $port = null): ?string
    {
        $ip = self::detectIpv4();
        if ($ip === null) {
            return null;
        }

        $port ??= (int) env('SERVER_PORT', 8001);

        return 'http://'.$ip.':'.$port;
    }

    public static function applyToRuntime(?int $port = null): ?string
    {
        $url = self::resolvePublicUrl($port);
        if ($url === null) {
            return null;
        }

        putenv('APP_PUBLIC_URL='.$url);
        $_ENV['APP_PUBLIC_URL'] = $url;
        $_SERVER['APP_PUBLIC_URL'] = $url;
        config(['app.public_url' => $url]);

        return $url;
    }
}
