<?php

namespace Tests\Unit;

use App\Models\MaquinaPlanta;
use App\Models\ProcesoMaquinaPlanta;
use App\Models\ProcesoPlanta;
use App\Support\MaquinaProcesoCompatibilidad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaquinaProcesoCompatibilidadTest extends TestCase
{
    use RefreshDatabase;

    public function test_lavadora_no_es_compatible_con_empaquetado_aunque_haya_vinculo_erroneo(): void
    {
        $empaquetado = ProcesoPlanta::create(['nombre' => 'Empaquetado', 'activo' => true]);
        $preparacion = ProcesoPlanta::create(['nombre' => 'Preparación de Materias Primas', 'activo' => true]);
        $lavadora = MaquinaPlanta::create([
            'nombre' => 'Lavadora Industrial L-100',
            'codigo' => 'L-100',
            'activo' => true,
        ]);

        ProcesoMaquinaPlanta::create([
            'procesoplantaid' => $empaquetado->procesoplantaid,
            'maquinaplantaid' => $lavadora->maquinaplantaid,
            'orden_paso' => 1,
            'nombre' => 'Vínculo erróneo',
        ]);

        $this->assertFalse(MaquinaProcesoCompatibilidad::compatible(
            (int) $empaquetado->procesoplantaid,
            (int) $lavadora->maquinaplantaid,
        ));
        $this->assertTrue(MaquinaProcesoCompatibilidad::compatible(
            (int) $preparacion->procesoplantaid,
            (int) $lavadora->maquinaplantaid,
        ));
    }

    public function test_maquina_personalizada_usa_vinculo_explicito(): void
    {
        $preparacion = ProcesoPlanta::create(['nombre' => 'Preparación de Materias Primas', 'activo' => true]);
        $mezclado = ProcesoPlanta::create(['nombre' => 'Mezclado', 'activo' => true]);
        $custom = MaquinaPlanta::create([
            'nombre' => 'Equipo especial XYZ',
            'codigo' => 'XYZ-99',
            'activo' => true,
        ]);

        ProcesoMaquinaPlanta::create([
            'procesoplantaid' => $preparacion->procesoplantaid,
            'maquinaplantaid' => $custom->maquinaplantaid,
            'orden_paso' => 1,
            'nombre' => 'Paso custom',
        ]);

        $this->assertTrue(MaquinaProcesoCompatibilidad::compatible(
            (int) $preparacion->procesoplantaid,
            (int) $custom->maquinaplantaid,
        ));
        $this->assertFalse(MaquinaProcesoCompatibilidad::compatible(
            (int) $mezclado->procesoplantaid,
            (int) $custom->maquinaplantaid,
        ));
    }
}
