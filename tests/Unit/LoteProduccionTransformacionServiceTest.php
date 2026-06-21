<?php

namespace Tests\Unit;

use App\Models\LoteProduccionPedido;
use App\Models\MaquinaPlanta;
use App\Models\Pedido;
use App\Models\PlantillaTransformacion;
use App\Models\PlantillaTransformacionPaso;
use App\Models\ProcesoMaquinaPlanta;
use App\Models\ProcesoPlanta;
use App\Models\RegistroProcesoMaquinaPlanta;
use App\Models\Usuario;
use App\Support\LoteProduccionTransformacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoteProduccionTransformacionServiceTest extends TestCase
{
    use RefreshDatabase;

    private LoteProduccionTransformacionService $service;

    private int $usuarioId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LoteProduccionTransformacionService;
        $this->usuarioId = Usuario::create([
            'nombre' => 'Test',
            'apellido' => 'Operador',
            'email' => 'test-'.uniqid().'@example.com',
            'nombreusuario' => 'test_'.uniqid(),
            'passwordhash' => 'hash',
        ])->usuarioid;
    }

    public function test_con_plantilla_no_completa_si_falta_empaquetado(): void
    {
        [$lote, $vinculos] = $this->crearLoteConPlantillaDeTresPasos();
        $this->registrarEtapa($lote, $vinculos[0]);
        $this->registrarEtapa($lote, $vinculos[1]);

        $this->assertFalse($this->service->transformacionCompleta($lote));
        $this->assertFalse($this->service->plantillaAgotada($lote));
    }

    public function test_con_plantilla_no_completa_si_solo_registra_empaquetado_antes_de_todos_los_pasos(): void
    {
        [$lote, $vinculos] = $this->crearLoteConPlantillaDeTresPasos();
        $this->registrarEtapa($lote, $vinculos[2]);

        $this->assertFalse($this->service->transformacionCompleta($lote));
    }

    public function test_con_plantilla_completa_al_terminar_empaquetado_como_ultimo_paso(): void
    {
        [$lote, $vinculos] = $this->crearLoteConPlantillaDeTresPasos();
        foreach ($vinculos as $vinculo) {
            $this->registrarEtapa($lote, $vinculo);
        }

        $lote->refresh();

        $this->assertTrue($this->service->transformacionCompleta($lote));
        $this->assertTrue($this->service->plantillaAgotada($lote));
    }

    public function test_sin_plantilla_completa_solo_con_empaquetado(): void
    {
        $empaquetado = ProcesoPlanta::create(['nombre' => 'Empaquetado', 'activo' => true]);
        $maquina = MaquinaPlanta::create(['nombre' => 'Selladora', 'codigo' => 'SE-10', 'activo' => true]);
        $vinculo = ProcesoMaquinaPlanta::create([
            'procesoplantaid' => $empaquetado->procesoplantaid,
            'maquinaplantaid' => $maquina->maquinaplantaid,
            'orden_paso' => 1,
            'nombre' => 'Empaquetado',
        ]);

        $lote = $this->crearLote();
        $this->registrarEtapa($lote, $vinculo);

        $this->assertTrue($this->service->transformacionCompleta($lote));
    }

    /**
     * @return array{0: LoteProduccionPedido, 1: list<ProcesoMaquinaPlanta>}
     */
    private function crearLoteConPlantillaDeTresPasos(): array
    {
        $preparacion = ProcesoPlanta::create(['nombre' => 'Preparación de Materias Primas', 'activo' => true]);
        $envasado = ProcesoPlanta::create(['nombre' => 'Envasado', 'activo' => true]);
        $empaquetado = ProcesoPlanta::create(['nombre' => 'Empaquetado', 'activo' => true]);
        $maquina = MaquinaPlanta::create(['nombre' => 'Línea test', 'codigo' => 'T-01', 'activo' => true]);

        $vinculos = [];
        foreach ([$preparacion, $envasado, $empaquetado] as $i => $proceso) {
            $vinculos[] = ProcesoMaquinaPlanta::create([
                'procesoplantaid' => $proceso->procesoplantaid,
                'maquinaplantaid' => $maquina->maquinaplantaid,
                'orden_paso' => $i + 1,
                'nombre' => $proceso->nombre,
            ]);
        }

        $plantilla = PlantillaTransformacion::create([
            'nombre' => 'Proceso test IQF',
            'producto_ejemplo' => 'Cebolla en cubos',
            'palabras_clave' => json_encode(['cebolla', 'cubo', 'iqf']),
            'activo' => true,
        ]);

        foreach ($vinculos as $i => $vinculo) {
            PlantillaTransformacionPaso::create([
                'plantillatransformacionid' => $plantilla->plantillatransformacionid,
                'orden' => $i + 1,
                'procesoplantaid' => $vinculo->procesoplantaid,
                'maquinaplantaid' => $vinculo->maquinaplantaid,
            ]);
        }

        $lote = $this->crearLote();
        $lote->update(['plantillatransformacionid' => $plantilla->plantillatransformacionid]);

        return [$lote->fresh(), $vinculos];
    }

    private function crearLote(): LoteProduccionPedido
    {
        $pedido = Pedido::create([
            'numero_solicitud' => 'TEST-'.uniqid(),
            'nombre_planta' => 'Planta test',
            'latitud' => -12.0,
            'longitud' => -77.0,
            'estado' => 'en produccion',
        ]);

        return LoteProduccionPedido::create([
            'pedidoid' => $pedido->pedidoid,
            'codigo_lote' => 'LOTE-'.uniqid(),
            'nombre' => 'Lote test',
            'producto' => 'Cebolla en cubos IQF',
            'fecha_creacion' => now()->toDateString(),
        ]);
    }

    private function registrarEtapa(LoteProduccionPedido $lote, ProcesoMaquinaPlanta $vinculo): void
    {
        RegistroProcesoMaquinaPlanta::create([
            'procesomaquinaplantaid' => $vinculo->procesomaquinaplantaid,
            'loteproduccionpedidoid' => $lote->loteproduccionpedidoid,
            'usuarioid' => $this->usuarioId,
            'variables_ingresadas' => '{}',
            'cumple_estandar' => true,
            'hora_inicio' => now(),
            'hora_fin' => now()->addHour(),
            'fecha_registro' => now(),
        ]);
    }
}
