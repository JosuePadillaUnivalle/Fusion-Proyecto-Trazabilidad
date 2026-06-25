<?php

namespace Tests\Unit;

use App\Support\EstadoLoteCatalogo;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EstadoLoteCatalogoEliminacionTest extends TestCase
{
    #[DataProvider('estadosEliminacionProvider')]
    public function test_lote_solo_se_puede_eliminar_en_planificado(string $nombreEstado, bool $esperado): void
    {
        $this->assertSame($esperado, EstadoLoteCatalogo::loteSePuedeEliminar($nombreEstado));
    }

    public static function estadosEliminacionProvider(): array
    {
        return [
            'planificado' => ['Planificado', true],
            'disponible legacy' => ['Disponible', true],
            'en crecimiento' => ['En crecimiento', false],
            'sembrado' => ['Sembrado', false],
            'cosechado' => ['Cosechado', false],
            'finalizado' => ['Finalizado', false],
            'certificado' => ['Certificado', false],
        ];
    }
}
