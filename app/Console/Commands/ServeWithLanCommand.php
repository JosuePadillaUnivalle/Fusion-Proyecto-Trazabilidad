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
            ['host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on (auto = IP WiFi actual)', Env::get('SERVER_HOST', 'auto')],
            ['port', null, InputOption::VALUE_OPTIONAL, 'The port to serve the application on', Env::get('SERVER_PORT')],
            ['tries', null, InputOption::VALUE_OPTIONAL, 'The max number of ports to attempt to serve from', 10],
            ['no-reload', null, InputOption::VALUE_NONE, 'Do not reload the development server on .env file changes'],
        ];
    }

    public function handle()
    {
        $this->aplicarHostWifiActual();

        $port = (int) $this->port();
        $lanUrl = LanNetworkResolver::applyToRuntime($port);

        if ($lanUrl) {
            config(['app.url' => $lanUrl]);
            $this->components->info('AgroFusion disponible en la red WiFi: '.$lanUrl);
            $this->components->twoColumnDetail('QR / celular (misma WiFi)', $lanUrl);
        } else {
            $this->components->warn('No se detectó IP WiFi. El servidor usará '.$this->host().':'.$port);
        }

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

    private function aplicarHostWifiActual(): void
    {
        if ($this->hostExplicitoEnCli()) {
            return;
        }

        $host = (string) ($this->input->getOption('host') ?? 'auto');
        if (! in_array($host, ['auto', '0.0.0.0', '127.0.0.1', 'localhost', ''], true)) {
            return;
        }

        $lanIp = LanNetworkResolver::detectIpv4();
        if ($lanIp !== null) {
            $this->input->setOption('host', $lanIp);

            return;
        }

        if ($host === 'auto') {
            $this->input->setOption('host', '0.0.0.0');
        }
    }

    private function hostExplicitoEnCli(): bool
    {
        foreach ($_SERVER['argv'] ?? [] as $arg) {
            if (str_starts_with((string) $arg, '--host=') || $arg === '--host') {
                return true;
            }
        }

        return false;
    }
}
