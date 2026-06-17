<?php

namespace Tests\Unit;

use App\Models\PedidoDistribucion;
use App\Support\PedidoDistribucionCatalogo;
use PHPUnit\Framework\TestCase;

/**
 * Prueba unitaria 1 — Filtro de estados en Pedidos de distribución (AgroFusion).
 *
 * Funcionalidad: agrupación de estados técnicos en filtros intuitivos
 * (En revisión, En camino, Recibido, etc.) en Punto de venta → Pedidos de distribución.
 */
class PedidoDistribucionCatalogoTest extends TestCase
{
    public function test_etiquetas_filtro_estado_son_intuitivas_y_no_tecnicas(): void
    {
        $etiquetas = PedidoDistribucionCatalogo::etiquetasFiltroEstado();

        $this->assertArrayHasKey('revision', $etiquetas);
        $this->assertSame('En revisión', $etiquetas['revision']);
        $this->assertSame('En camino', $etiquetas['camino']);
        $this->assertSame('Recibido', $etiquetas['recibido']);

        foreach ($etiquetas as $texto) {
            $this->assertStringNotContainsString('_', $texto);
        }
    }

    public function test_grupo_camino_incluye_solo_estado_en_transito(): void
    {
        $estados = PedidoDistribucionCatalogo::estadosDeGrupoFiltro('camino');

        $this->assertSame(
            [PedidoDistribucionCatalogo::ESTADO_EN_TRANSITO],
            $estados
        );
    }

    public function test_grupo_cerrado_agrupa_rechazado_y_cancelado(): void
    {
        $estados = PedidoDistribucionCatalogo::estadosDeGrupoFiltro('cerrado');

        $this->assertContains(PedidoDistribucionCatalogo::ESTADO_RECHAZADO, $estados);
        $this->assertContains(PedidoDistribucionCatalogo::ESTADO_CANCELADO, $estados);
        $this->assertCount(2, $estados);
    }

    public function test_badge_estado_muestra_etiqueta_legible_en_camino(): void
    {
        $pedido = new PedidoDistribucion([
            'estado' => PedidoDistribucionCatalogo::ESTADO_EN_TRANSITO,
        ]);

        $badge = PedidoDistribucionCatalogo::badgeEstado($pedido);

        $this->assertSame('En camino', $badge['etiqueta']);
        $this->assertSame('primary', $badge['clase']);
    }

    public function test_puede_confirmar_recepcion_solo_si_esta_en_transito(): void
    {
        $enTransito = new PedidoDistribucion([
            'estado' => PedidoDistribucionCatalogo::ESTADO_EN_TRANSITO,
        ]);
        $recibido = new PedidoDistribucion([
            'estado' => PedidoDistribucionCatalogo::ESTADO_RECIBIDO,
        ]);

        $this->assertTrue(PedidoDistribucionCatalogo::puedeConfirmarRecepcion($enTransito));
        $this->assertFalse(PedidoDistribucionCatalogo::puedeConfirmarRecepcion($recibido));
    }
}
