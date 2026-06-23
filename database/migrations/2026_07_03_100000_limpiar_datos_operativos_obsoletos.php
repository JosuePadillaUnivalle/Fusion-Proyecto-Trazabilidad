<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    private array $pedidosAgricola = [
        'MOD-PED-002',
        'MOD-PED-005',
        'MOD-PED-007',
        'DEMO-B5-PED-002',
        'DEMO-B5-PED-005',
    ];

    /** @var list<string> */
    private array $externoEnvio = [
        'ENV-MOD-26-05',
        'ENV-MOD-26-02',
        'DEMO-B5-PED-005',
        'DEMO-B5-PED-002',
    ];

    /** @var list<string> */
    private array $pedidosPdv = [
        'PDV-20260622-0001',
        'PDV-20260622-0002',
    ];

    /** @var list<string> */
    private array $lotesTransformacion = [
        'LOTE-0003-20260609',
        'LOTE-0004-20260609',
        'LOTE-0005-20260610',
    ];

    public function up(): void
    {
        foreach ($this->pedidosPdv as $numero) {
            $this->eliminarPedidoDistribucion($numero);
        }

        foreach ($this->pedidosAgricola as $numero) {
            $this->eliminarPedidoAgricola($numero);
        }

        foreach ($this->externoEnvio as $externo) {
            $this->eliminarEnvioPorExterno($externo);
        }

        foreach ($this->lotesTransformacion as $codigo) {
            $this->eliminarLoteProduccion($codigo);
        }
    }

    public function down(): void
    {
        // Limpieza operativa; no reversible.
    }

    private function eliminarPedidoDistribucion(string $numero): void
    {
        if (! Schema::hasTable('pedido_distribucion')) {
            return;
        }

        $id = DB::table('pedido_distribucion')->where('numero_solicitud', $numero)->value('pedidodistribucionid');
        if ($id === null) {
            return;
        }

        if (Schema::hasTable('solicitud_produccion_planta')) {
            DB::table('solicitud_produccion_planta')->where('pedidodistribucionid', $id)->delete();
        }
        if (Schema::hasTable('detalle_pedido_distribucion')) {
            DB::table('detalle_pedido_distribucion')->where('pedidodistribucionid', $id)->delete();
        }
        if (Schema::hasTable('ruta_distribucion_parada')) {
            DB::table('ruta_distribucion_parada')->where('pedidodistribucionid', $id)->update(['pedidodistribucionid' => null]);
        }
        if (Schema::hasTable('almacen_movimiento')) {
            DB::table('almacen_movimiento')->where('referencia', $numero)->delete();
        }

        DB::table('pedido_distribucion')->where('pedidodistribucionid', $id)->delete();
    }

    private function eliminarPedidoAgricola(string $numero): void
    {
        if (! Schema::hasTable('pedido')) {
            return;
        }

        $pedidoid = DB::table('pedido')->where('numero_solicitud', $numero)->value('pedidoid');
        if ($pedidoid === null) {
            return;
        }

        $this->eliminarEnvioPorPedido((int) $pedidoid);

        if (Schema::hasTable('lote_produccion_pedido')) {
            $loteIds = DB::table('lote_produccion_pedido')->where('pedidoid', $pedidoid)->pluck('loteproduccionpedidoid');
            foreach ($loteIds as $loteId) {
                $codigo = DB::table('lote_produccion_pedido')->where('loteproduccionpedidoid', $loteId)->value('codigo_lote');
                if (is_string($codigo) && $codigo !== '') {
                    $this->eliminarLoteProduccion($codigo);
                }
            }
        }

        if (Schema::hasTable('detallepedido')) {
            DB::table('detallepedido')->where('pedidoid', $pedidoid)->delete();
        }
        if (Schema::hasTable('documento_entrega')) {
            DB::table('documento_entrega')->where('pedidoid', $pedidoid)->update(['pedidoid' => null]);
        }

        DB::table('pedido')->where('pedidoid', $pedidoid)->delete();
    }

    private function eliminarEnvioPorExterno(string $externo): void
    {
        if (! Schema::hasTable('envio_asignacion_multiple')) {
            return;
        }

        $asignacionIds = DB::table('envio_asignacion_multiple')
            ->where('externo_envio_id', $externo)
            ->pluck('envioasignacionmultipleid');

        foreach ($asignacionIds as $asignacionId) {
            $this->eliminarAsignacion((int) $asignacionId);
        }
    }

    private function eliminarEnvioPorPedido(int $pedidoid): void
    {
        if (! Schema::hasTable('envio_asignacion_multiple')) {
            return;
        }

        $asignacionIds = DB::table('envio_asignacion_multiple')
            ->where('pedidoid', $pedidoid)
            ->pluck('envioasignacionmultipleid');

        foreach ($asignacionIds as $asignacionId) {
            $this->eliminarAsignacion((int) $asignacionId);
        }
    }

    private function eliminarAsignacion(int $asignacionId): void
    {
        if (Schema::hasTable('historial_estado_envio')) {
            DB::table('historial_estado_envio')->where('envioasignacionmultipleid', $asignacionId)->delete();
        }
        if (Schema::hasTable('incidente_envio')) {
            DB::table('incidente_envio')->where('envioasignacionmultipleid', $asignacionId)->delete();
        }
        if (Schema::hasTable('firma_transportista_envio')) {
            DB::table('firma_transportista_envio')->where('envioasignacionmultipleid', $asignacionId)->delete();
        }
        if (Schema::hasTable('firma_recepcion_envio')) {
            DB::table('firma_recepcion_envio')->where('envioasignacionmultipleid', $asignacionId)->delete();
        }
        if (Schema::hasTable('simulacion_ruta_transporte')) {
            DB::table('simulacion_ruta_transporte')->where('envioasignacionmultipleid', $asignacionId)->delete();
        }
        if (Schema::hasTable('documento_entrega')) {
            DB::table('documento_entrega')
                ->where('metadata->envioasignacionmultipleid', $asignacionId)
                ->orWhere('metadata->envio_asignacion_id', $asignacionId)
                ->update(['pedidoid' => null]);
        }

        DB::table('envio_asignacion_multiple')->where('envioasignacionmultipleid', $asignacionId)->delete();
    }

    private function eliminarLoteProduccion(string $codigo): void
    {
        if (! Schema::hasTable('lote_produccion_pedido')) {
            return;
        }

        $loteId = DB::table('lote_produccion_pedido')->where('codigo_lote', $codigo)->value('loteproduccionpedidoid');
        if ($loteId === null) {
            return;
        }

        if (Schema::hasTable('asignacion_etapa_planta')) {
            DB::table('asignacion_etapa_planta')->where('loteproduccionpedidoid', $loteId)->delete();
        }
        if (Schema::hasTable('registro_proceso_maquina_planta')) {
            DB::table('registro_proceso_maquina_planta')->where('loteproduccionpedidoid', $loteId)->delete();
        }
        if (Schema::hasTable('evaluacion_final_lote_produccion')) {
            DB::table('evaluacion_final_lote_produccion')->where('loteproduccionpedidoid', $loteId)->delete();
        }
        if (Schema::hasTable('almacenaje_lote_produccion')) {
            DB::table('almacenaje_lote_produccion')->where('loteproduccionpedidoid', $loteId)->delete();
        }
        if (Schema::hasTable('lote_produccion_materia_prima')) {
            DB::table('lote_produccion_materia_prima')->where('loteproduccionpedidoid', $loteId)->delete();
        }
        if (Schema::hasTable('certificacion_lote_produccion')) {
            DB::table('certificacion_lote_produccion')->where('loteproduccionpedidoid', $loteId)->delete();
        }
        if (Schema::hasTable('inventario_presentacion_lote')) {
            DB::table('inventario_presentacion_lote')->where('loteproduccionpedidoid', $loteId)->delete();
        }
        if (Schema::hasTable('almacen_movimiento')) {
            DB::table('almacen_movimiento')->where('loteproduccionpedidoid', $loteId)->delete();
        }
        if (Schema::hasTable('detalle_traslado_planta_mayorista')) {
            DB::table('detalle_traslado_planta_mayorista')->where('loteproduccionpedidoid', $loteId)->update(['loteproduccionpedidoid' => null]);
        }

        DB::table('lote_produccion_pedido')->where('loteproduccionpedidoid', $loteId)->delete();
    }
};
