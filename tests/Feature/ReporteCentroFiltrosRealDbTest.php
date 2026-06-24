<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Tests\TestCase;

class ReporteCentroFiltrosRealDbTest extends TestCase
{
    public function test_reportes_con_filtros_en_bd_real(): void
    {
        if (config('database.connections.sqlite.database') === ':memory:') {
            $this->markTestSkipped('Solo aplica con BD SQLite persistida (no en CI)');
        }

        $admin = Usuario::query()
            ->where('activo', true)
            ->where(function ($q) {
                $q->where('role', 'admin')->orWhere('nombreusuario', 'admin');
            })
            ->first();

        if ($admin === null) {
            $this->markTestSkipped('Sin usuario admin en BD local');
        }

        $cases = [
            ['reportes.envios-estado', ['fecha_desde' => '2026-05-01', 'fecha_hasta' => '2026-06-23']],
            ['reportes.envios-estado', ['fecha_desde' => '2026-05-01', 'fecha_hasta' => '2026-06-23', 'estado_envio' => '']],
            ['reportes.traslados-planta-mayorista', ['fecha_desde' => '2026-05-01', 'fecha_hasta' => '2026-06-23', 'estado' => '']],
            ['reportes.stock-ambito', ['ambito' => '']],
            ['reportes.transportistas', ['fecha_desde' => '2026-05-01', 'fecha_hasta' => '2026-06-23', 'transportista' => '']],
        ];

        foreach ($cases as [$route, $query]) {
            $response = $this->actingAs($admin)->get(route($route, $query));
            $response->assertOk("{$route} status ".$response->status());
            $response->assertSee('rpt-kpi', false);
            $response->assertDontSee('ViewException', false);
            $response->assertDontSee('Undefined variable', false);
        }

        $traslados = $this->actingAs($admin)->get(route('reportes.traslados-planta-mayorista', [
            'fecha_desde' => '2026-05-01',
            'fecha_hasta' => '2026-06-23',
        ]));
        $traslados->assertOk();
        $traslados->assertSee('Listado de traslados', false);
        $traslados->assertSee('rptChart', false);

        $html = $traslados->getContent();
        $this->assertStringNotContainsString('</script><script', $html);
    }
}
