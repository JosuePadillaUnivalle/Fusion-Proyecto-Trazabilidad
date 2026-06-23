<?php

namespace App\Console\Commands;

use App\Support\LanNetworkResolver;
use Illuminate\Foundation\Console\ServeCommand;

class ServeWithLanCommand extends ServeCommand
{
    public function __construct()
    {
        parent::__construct();

        if (! in_array('APP_PUBLIC_URL', static::$passthroughVariables, true)) {
            static::$passthroughVariables[] = 'APP_PUBLIC_URL';
        }
    }

    public function handle()
    {
        $port = (int) $this->port();
        $lanUrl = LanNetworkResolver::applyToRuntime($port);

        if ($lanUrl) {
            $this->components->twoColumnDetail('QR / celular (misma WiFi)', $lanUrl);
        } else {
            $this->components->warn('No se detectó IP de red local. Defina APP_PUBLIC_URL en .env para el QR del celular.');
        }

        $this->components->twoColumnDetail('PC (contraseñas Chrome)', 'http://127.0.0.1:'.$port);
        $this->components->warn('Use siempre la misma URL en el navegador (127.0.0.1 o IP WiFi, no ambas) para no perder la sesión.');

        return parent::handle();
    }

    protected function startProcess($hasEnvironment)
    {
        putenv('PHP_CLI_SERVER_WORKERS=1');
        $_ENV['PHP_CLI_SERVER_WORKERS'] = '1';
        $_SERVER['PHP_CLI_SERVER_WORKERS'] = '1';

        $lanUrl = LanNetworkResolver::resolvePublicUrl((int) $this->port());
        if ($lanUrl) {
            putenv('APP_PUBLIC_URL='.$lanUrl);
            $_ENV['APP_PUBLIC_URL'] = $lanUrl;
            $_SERVER['APP_PUBLIC_URL'] = $lanUrl;
        }

        return parent::startProcess($hasEnvironment);
    }
}
