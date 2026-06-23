<?php

namespace Tests\Unit;

use App\Models\MaquinaPlanta;
use App\Models\PlantillaTransformacion;
use App\Models\PlantillaTransformacionPaso;
use App\Models\ProcesoMaquinaPlanta;
use App\Models\ProcesoPlanta;
use App\Support\PlantillaTransformacionDisponibilidad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlantillaTransformacionDisponibilidadTest extends TestCase
{
    use RefreshDatabase;

    public function test_maquina_especifica_en_mantenimiento_bloquea_proceso(): void
    {
        $plantilla = $this->plantillaConPaso(maquinaActiva: false, maquinaEspecifica: true);

        $this->assertTrue(PlantillaTransformacionDisponibilidad::plantillaBloqueada($plantilla));
    }

    public function test_cualquiera_compatible_con_una_maquina_activa_no_bloquea(): void
    {
        $proceso = ProcesoPlanta::create(['nombre' => 'Empaquetado', 'activo' => true]);
        $activa = $this->crearMaquinaEmpaquetadora('SE-10', true);
        $mantenimiento = $this->crearMaquinaEmpaquetadora('SE-11', false);

        ProcesoMaquinaPlanta::create([
            'procesoplantaid' => $proceso->procesoplantaid,
            'maquinaplantaid' => $activa->maquinaplantaid,
            'orden_paso' => 1,
            'nombre' => 'Empaquetado',
        ]);
        ProcesoMaquinaPlanta::create([
            'procesoplantaid' => $proceso->procesoplantaid,
            'maquinaplantaid' => $mantenimiento->maquinaplantaid,
            'orden_paso' => 2,
            'nombre' => 'Empaquetado',
        ]);

        $plantilla = PlantillaTransformacion::create(['nombre' => 'Proceso empaque', 'activo' => true]);
        PlantillaTransformacionPaso::create([
            'plantillatransformacionid' => $plantilla->plantillatransformacionid,
            'orden' => 1,
            'procesoplantaid' => $proceso->procesoplantaid,
            'maquinaplantaid' => null,
        ]);

        $plantilla->load(['pasos.proceso', 'pasos.maquina']);

        $this->assertFalse(PlantillaTransformacionDisponibilidad::plantillaBloqueada($plantilla));
    }

    public function test_cualquiera_compatible_con_todas_en_mantenimiento_bloquea(): void
    {
        $proceso = ProcesoPlanta::create(['nombre' => 'Empaquetado', 'activo' => true]);
        $m1 = $this->crearMaquinaEmpaquetadora('SE-10', false);
        $m2 = $this->crearMaquinaEmpaquetadora('SE-11', false);

        foreach ([$m1, $m2] as $i => $maq) {
            ProcesoMaquinaPlanta::create([
                'procesoplantaid' => $proceso->procesoplantaid,
                'maquinaplantaid' => $maq->maquinaplantaid,
                'orden_paso' => $i + 1,
                'nombre' => 'Empaquetado',
            ]);
        }

        $plantilla = PlantillaTransformacion::create(['nombre' => 'Proceso bloqueado', 'activo' => true]);
        PlantillaTransformacionPaso::create([
            'plantillatransformacionid' => $plantilla->plantillatransformacionid,
            'orden' => 1,
            'procesoplantaid' => $proceso->procesoplantaid,
            'maquinaplantaid' => null,
        ]);

        $plantilla->load(['pasos.proceso', 'pasos.maquina']);

        $this->assertTrue(PlantillaTransformacionDisponibilidad::plantillaBloqueada($plantilla));
    }

    private function plantillaConPaso(bool $maquinaActiva, bool $maquinaEspecifica): PlantillaTransformacion
    {
        $proceso = ProcesoPlanta::create(['nombre' => 'Envasado', 'activo' => true]);
        $maquina = MaquinaPlanta::create([
            'nombre' => 'Envasadora test',
            'codigo' => 'EV-700',
            'activo' => $maquinaActiva,
        ]);

        $plantilla = PlantillaTransformacion::create(['nombre' => 'Test', 'activo' => true]);
        PlantillaTransformacionPaso::create([
            'plantillatransformacionid' => $plantilla->plantillatransformacionid,
            'orden' => 1,
            'procesoplantaid' => $proceso->procesoplantaid,
            'maquinaplantaid' => $maquinaEspecifica ? $maquina->maquinaplantaid : null,
        ]);

        $plantilla->load(['pasos.proceso', 'pasos.maquina']);

        return $plantilla;
    }

    private function crearMaquinaEmpaquetadora(string $codigo, bool $activa): MaquinaPlanta
    {
        return MaquinaPlanta::create([
            'nombre' => 'Selladora '.$codigo,
            'codigo' => $codigo,
            'activo' => $activa,
        ]);
    }
}
