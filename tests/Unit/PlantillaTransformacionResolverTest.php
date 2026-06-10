<?php

namespace Tests\Unit;

use App\Models\PlantillaTransformacion;
use App\Support\PlantillaTransformacionResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlantillaTransformacionResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_resuelve_plantilla_por_producto_pure_de_papa(): void
    {
        $plantilla = PlantillaTransformacion::create([
            'nombre' => 'Puré de papa test',
            'producto_ejemplo' => 'Puré de papa',
            'palabras_clave' => json_encode(['puré', 'papa']),
            'activo' => true,
        ]);

        $resuelta = PlantillaTransformacionResolver::resolverPorProducto('Puré de papa industrial');

        $this->assertNotNull($resuelta);
        $this->assertSame($plantilla->plantillatransformacionid, $resuelta->plantillatransformacionid);
    }
}
