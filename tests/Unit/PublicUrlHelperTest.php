<?php

namespace Tests\Unit;

use App\Support\PublicUrlHelper;
use Illuminate\Http\Request;
use Tests\TestCase;

class PublicUrlHelperTest extends TestCase
{
    public function test_qr_usa_app_public_url_aunque_el_navegador_este_en_loopback(): void
    {
        config(['app.public_url' => 'http://192.168.1.50:8001']);
        $request = Request::create('http://127.0.0.1:8001/recepcion/demo', 'GET');
        $this->app->instance('request', $request);

        $url = PublicUrlHelper::absoluteForQr('/recepcion/abc123');

        $this->assertSame('http://192.168.1.50:8001/recepcion/abc123', $url);
        $this->assertStringNotContainsString('127.0.0.1', $url);
    }
}
