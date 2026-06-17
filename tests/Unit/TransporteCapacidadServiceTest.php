<?php

namespace Tests\Unit;

use App\Services\TransporteCapacidadService;
use App\Support\EstadoVehiculoCatalogo;
use App\Support\LicenciaConduccionCatalogo;
use App\Models\EstadoVehiculo;
use App\Models\Vehiculo;
use PHPUnit\Framework\TestCase;

class TransporteCapacidadServiceTest extends TestCase
{
    public function test_licencia_particular_no_autoriza_camion(): void
    {
        $this->assertFalse(LicenciaConduccionCatalogo::puedeConducir('P', 'C'));
        $this->assertTrue(LicenciaConduccionCatalogo::puedeConducir('C', 'B'));
        $this->assertTrue(LicenciaConduccionCatalogo::puedeConducirConLicencias(['A', 'B'], 'B'));
    }

    public function test_volumen_estimado_desde_peso(): void
    {
        $svc = new TransporteCapacidadService;
        $this->assertSame(5.0, $svc->volumenDesdePeso(1000));
    }

    public function test_vehiculo_en_mantenimiento_no_disponible(): void
    {
        $vehiculo = new Vehiculo(['activo' => true, 'placa' => 'TEST-01']);
        $vehiculo->setRelation('estadoVehiculo', new EstadoVehiculo(['nombre' => 'mantenimiento']));

        $this->assertTrue(EstadoVehiculoCatalogo::enMantenimiento($vehiculo));
        $this->assertFalse(EstadoVehiculoCatalogo::disponibleParaUso($vehiculo));
    }

    public function test_codigo_visual_prioriza_en_ruta(): void
    {
        $svc = new \App\Services\VehiculoFlotaEstadoService;
        $vehiculo = new Vehiculo(['activo' => true, 'placa' => 'SCZ-001', 'vehiculoid' => 99]);
        $vehiculo->setRelation('estadoVehiculo', new EstadoVehiculo(['nombre' => 'operativo']));

        $mapa = ['placas' => ['SCZ-001' => true], 'ids' => []];

        $this->assertSame('en_ruta', $svc->codigoVisual($vehiculo, $mapa));
        $this->assertSame('En ruta', $svc->etiquetaVisual($vehiculo, $mapa));
    }
}
