<?php

namespace Tests\Unit;

use App\Support\LoteTrazabilidadService;
use Illuminate\Support\Collection;
use ReflectionClass;
use Tests\TestCase;

class LoteTrazabilidadHistorialOrdenTest extends TestCase
{
    public function test_orden_historial_respeta_ciclo_del_lote(): void
    {
        $svc = app(LoteTrazabilidadService::class);
        $ref = new ReflectionClass($svc);
        $numerar = $ref->getMethod('numerarYOrdenarEventosHistorial');
        $numerar->setAccessible(true);

        /** @var Collection<int, array<string, mixed>> $eventos */
        $eventos = collect([
            $this->eventoPrueba('2026-06-24 20:26:00', 'certificacion', 'certificacion', 'Certificación de lote'),
            $this->eventoPrueba('2026-06-24 20:26:00', 'cosecha', 'cosecha', 'Cosechado'),
            $this->eventoPrueba('2026-06-24 19:30:00', 'actividad', 'en_crecimiento', 'Fertilización', 3),
            $this->eventoPrueba('2026-06-24 12:38:00', 'actividad', 'en_crecimiento', 'Control de plagas', 1),
            $this->eventoPrueba('2026-06-24 12:09:00', 'siembra', 'siembra', 'Siembra de Cebolla'),
            $this->eventoPrueba('2026-06-24 11:56:00', 'estado', 'preparacion', 'En planificación'),
        ]);

        /** @var Collection<int, array<string, mixed>> $ordenados */
        $ordenados = $numerar->invoke($svc, $eventos);
        $titulos = $ordenados->pluck('titulo')->values()->all();
        $pasos = $ordenados->pluck('paso')->values()->all();

        $this->assertSame([
            'Certificación de lote',
            'Cosechado',
            'Fertilización',
            'Control de plagas',
            'Siembra de Cebolla',
            'En planificación',
        ], $titulos);

        $this->assertSame([6, 5, 4, 3, 2, 1], $pasos);
    }

    /**
     * @return array<string, mixed>
     */
    private function eventoPrueba(
        string $fecha,
        string $tipo,
        string $fase,
        string $titulo,
        ?int $actividadid = null,
    ): array {
        return [
            'fecha' => $fecha,
            'fecha_fmt' => $fecha,
            'tipo' => $tipo,
            'fase' => $fase,
            'titulo' => $titulo,
            'descripcion' => '',
            'actividadid' => $actividadid,
        ];
    }
}
