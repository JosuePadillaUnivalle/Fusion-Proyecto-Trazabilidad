<?php

namespace Tests\Unit;

use App\Models\AsignacionEtapaPlanta;
use Tests\TestCase;

class AsignacionEtapaPlantaServiceTest extends TestCase
{
    public function test_asignacion_estados_pendiente_y_completada(): void
    {
        $asignacion = new AsignacionEtapaPlanta(['estado' => AsignacionEtapaPlanta::ESTADO_PENDIENTE]);
        $this->assertTrue($asignacion->estaPendiente());

        $asignacion->estado = AsignacionEtapaPlanta::ESTADO_COMPLETADA;
        $this->assertFalse($asignacion->estaPendiente());

        $asignacion->estado = AsignacionEtapaPlanta::ESTADO_PROGRAMADA;
        $this->assertTrue($asignacion->estaProgramada());
        $this->assertFalse($asignacion->estaPendiente());
    }
}

