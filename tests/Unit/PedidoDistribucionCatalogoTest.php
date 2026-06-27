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
        $this->assertSame('En ruta', $etiquetas['camino']);
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

    public function test_badge_estado_espera_confirmacion_minorista(): void
    {
        $pedido = new PedidoDistribucion([
            'estado' => PedidoDistribucionCatalogo::ESTADO_CONFIRMADO,
            'envio_iniciado_mayorista' => true,
            'fecha_confirmacion_minorista' => null,
            'transportista_usuarioid' => 10,
            'rutadistribucionid' => 5,
        ]);

        $badge = PedidoDistribucionCatalogo::badgeEstado($pedido);

        $this->assertSame('Esperando confirmación del minorista', $badge['etiqueta']);
        $this->assertSame('revision', $badge['clase']);
    }

    public function test_badge_estado_muestra_etiqueta_legible_listo_para_salida(): void
    {
        $pedido = new PedidoDistribucion([
            'estado' => PedidoDistribucionCatalogo::ESTADO_EN_TRANSITO,
        ]);

        $badge = PedidoDistribucionCatalogo::badgeEstado($pedido);

        $this->assertSame('Listo para salida', $badge['etiqueta']);
        $this->assertSame('asignado', $badge['clase']);
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

    public function test_paso_actual_flujo_espera_confirmacion_minorista_antes_de_en_ruta(): void
    {
        $pedido = new PedidoDistribucion([
            'estado' => PedidoDistribucionCatalogo::ESTADO_CONFIRMADO,
            'envio_iniciado_mayorista' => true,
            'fecha_confirmacion_minorista' => null,
            'transportista_usuarioid' => 10,
            'rutadistribucionid' => 5,
        ]);

        $this->assertTrue(PedidoDistribucionCatalogo::pendienteConfirmacionMinorista($pedido));
        $this->assertSame(3, PedidoDistribucionCatalogo::pasoActualFlujo($pedido));
    }

    public function test_pasos_flujo_envio_mayorista_no_marca_solicitud_ni_revision_completadas(): void
    {
        $pedido = new PedidoDistribucion([
            'estado' => PedidoDistribucionCatalogo::ESTADO_CONFIRMADO,
            'envio_iniciado_mayorista' => true,
            'fecha_confirmacion_minorista' => null,
            'transportista_usuarioid' => 10,
            'rutadistribucionid' => 5,
        ]);

        $pasos = PedidoDistribucionCatalogo::pasosFlujoUi($pedido, false, false, true);

        $this->assertFalse($pasos[0]['hecho']);
        $this->assertFalse($pasos[1]['hecho']);
        $this->assertTrue($pasos[2]['activo']);
        $this->assertSame('Confirmación minorista', $pasos[2]['label']);
        $this->assertFalse($pasos[3]['activo']);
    }

    public function test_panel_capacidad_oculto_mientras_espera_confirmacion_minorista(): void
    {
        $pedido = new PedidoDistribucion([
            'estado' => PedidoDistribucionCatalogo::ESTADO_CONFIRMADO,
            'envio_iniciado_mayorista' => true,
            'fecha_confirmacion_minorista' => null,
            'transportista_usuarioid' => 10,
        ]);

        $this->assertFalse(PedidoDistribucionCatalogo::mostrarPanelCapacidadVehiculo(
            $pedido,
            true,
            false,
            true,
        ));
    }
}
