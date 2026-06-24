<?php

namespace App\Console\Commands;

use App\Support\LanNetworkResolver;
use Illuminate\Foundation\Console\ServeCommand;
use Illuminate\Support\Env;
use Symfony\Component\Console\Input\InputOption;

class ServeWithLanCommand extends ServeCommand
{
    public function __construct()
    {
        parent::__construct();

        if (! in_array('APP_PUBLIC_URL', static::$passthroughVariables, true)) {
            static::$passthroughVariables[] = 'APP_PUBLIC_URL';
        }
    }

    protected function getOptions()
    {
        return [
            ['host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on', Env::get('SERVER_HOST', '0.0.0.0')],
            ['port', null, InputOption::VALUE_OPTIONAL, 'The port to serve the application on', Env::get('SERVER_PORT')],
            ['tries', null, InputOption::VALUE_OPTIONAL, 'The max number of ports to attempt to serve from', 10],
            ['no-reload', null, InputOption::VALUE_NONE, 'Do not reload the development server on .env file changes'],
        ];
    }

    public function handle()
    {
        $port = (int) $this->port();
        $lanUrl = LanNetworkResolver::applyToRuntime($port);

        if ($lanUrl) {
            $this->components->twoColumnDetail('QR / celular (misma WiFi)', $lanUrl);
        } else {
            $this->components->warn('No se detectó IP de red local. El QR del celular podría no abrir.');
        }

        $this->components->twoColumnDetail('PC en este equipo', 'http://127.0.0.1:'.$port);

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
