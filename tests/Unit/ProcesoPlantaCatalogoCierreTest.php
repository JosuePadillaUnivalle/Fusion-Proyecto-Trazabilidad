<?php

namespace Tests\Unit;

use App\Models\ProcesoPlanta;
use App\Support\ProcesoPlantaCatalogo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcesoPlantaCatalogoCierreTest extends TestCase
{
    use RefreshDatabase;

    public function test_rechaza_ultimo_paso_si_no_es_empaquetado(): void
    {
        $preparacion = ProcesoPlanta::create(['nombre' => 'Preparación de Materias Primas', 'activo' => true]);
        $envasado = ProcesoPlanta::create(['nombre' => 'Envasado', 'activo' => true]);
        ProcesoPlanta::create(['nombre' => 'Empaquetado', 'activo' => true]);

        $error = ProcesoPlantaCatalogo::errorSiUltimoPasoNoEsEmpaquetado([
            ['procesoplantaid' => $preparacion->procesoplantaid],
            ['procesoplantaid' => $envasado->procesoplantaid],
        ]);

        $this->assertNotNull($error);
        $this->assertStringContainsString('Empaquetado', $error);
    }

    public function test_acepta_ultimo_paso_empaquetado(): void
    {
        $preparacion = ProcesoPlanta::create(['nombre' => 'Preparación de Materias Primas', 'activo' => true]);
        $empaquetado = ProcesoPlanta::create(['nombre' => 'Empaquetado', 'activo' => true]);

        $error = ProcesoPlantaCatalogo::errorSiUltimoPasoNoEsEmpaquetado([
            ['procesoplantaid' => $preparacion->procesoplantaid],
            ['procesoplantaid' => $empaquetado->procesoplantaid],
        ]);

        $this->assertNull($error);
    }
}
