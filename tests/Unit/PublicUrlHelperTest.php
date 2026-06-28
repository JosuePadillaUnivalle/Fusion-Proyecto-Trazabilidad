<?php

namespace Tests\Unit;

use App\Support\PublicUrlHelper;
use Illuminate\Http\Request;
use Tests\TestCase;

class PublicUrlHelperTest extends TestCase
{
    public function test_qr_usa_app_public_url_cuando_el_host_no_es_loopback(): void
    {
        config(['app.public_url' => 'http://192.168.1.50:8001']);
        $request = Request::create('http://192.168.1.50:8001/recepcion/demo', 'GET');
        $this->app->instance('request', $request);

        $url = PublicUrlHelper::absoluteForQr('/recepcion/abc123');

        $this->assertSame('http://192.168.1.50:8001/recepcion/abc123', $url);
    }

    public function test_qr_desde_loopback_usa_ip_lan_detectada(): void
    {
        config(['app.public_url' => 'http://10.26.12.121:8001']);
        $request = Request::create('http://127.0.0.1:8001/recepcion/demo', 'GET');
        $this->app->instance('request', $request);

        $detectada = \App\Support\LanNetworkResolver::detectIpv4();
        if ($detectada === null) {
            $this->markTestSkipped('No hay IP LAN detectable en este entorno.');
        }

        $url = PublicUrlHelper::absoluteForQr('/recepcion/abc123');

        $this->assertStringContainsString($detectada, $url);
        $this->assertStringNotContainsString('127.0.0.1', $url);
    }

    public function test_qr_en_railway_usa_dominio_publico_aunque_app_public_url_sea_lan(): void
    {
        putenv('RAILWAY_ENVIRONMENT=production');
        config([
            'app.public_url' => 'http://192.168.26.3:8001',
            'app.url' => 'https://agronexus-api-production.up.railway.app',
        ]);

        $url = PublicUrlHelper::absoluteForQr('/recepcion/abc123');

        $this->assertSame(
            'https://agronexus-api-production.up.railway.app/recepcion/abc123',
            $url
        );

        putenv('RAILWAY_ENVIRONMENT');
    }
}
