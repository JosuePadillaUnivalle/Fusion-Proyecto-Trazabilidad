<?php

namespace App\Support;

use App\Models\RutaDistribucion;

/** Textos de UI compartidos entre cierres operativos de rutas de distribución. */
final class EnvioCierreRutaUiCatalogo
{
    /** @return array<string, mixed> */
    public static function plantaMayorista(): array
    {
        return [
            'recibido_key' => 'recibido_planta',
            'toolbar_titulo' => 'Traslado planta → almacén mayorista',
            'recepcion_titulo' => 'Recepción en almacén mayorista',
            'recepcion_espera_info' => 'El transportista completa condiciones, llegada e incidentes. Le avisaremos cuando pueda firmar la recepción.',
            'destino_label' => 'almacén mayorista',
            'destino_articulo' => 'el almacén mayorista',
            'origen_salida' => 'planta',
            'tipo_tiempo_real' => 'planta_mayorista',
            'firma_recepcion_titulo' => 'Firma de recepción en almacén mayorista',
            'firma_recepcion_hint' => 'Firma de quien recibe la carga en el almacén mayorista.',
            'mensaje_finalizar' => '¿Confirma finalizar el traslado? Se transferirá el inventario al almacén mayorista.',
            'recibido_mensaje' => 'Recibido en almacén mayorista correctamente.',
            'documento_mensaje' => 'El traslado fue recibido en el almacén mayorista y el documento quedó registrado.',
            'llegada_titulo' => 'Confirmar llegada a almacén mayorista',
            'mensaje_espera_transportista' => 'No puede confirmar la llegada hasta estar físicamente en el almacén mayorista.',
        ];
    }

    /** @return array<string, mixed> */
    public static function mayoristaPdv(RutaDistribucion $ruta): array
    {
        $ruta->loadMissing(['paradas', 'pedidos.puntoVenta']);
        $pdv = $ruta->paradas?->firstWhere('tipo', RutaDistribucionCatalogo::PARADA_ENTREGA_PDV);
        $nombrePdv = $pdv
            ? str_replace('Entrega: ', '', (string) $pdv->destino)
            : ($ruta->pedidos->first()?->puntoVenta?->nombre ?? 'punto de venta');

        return [
            'recibido_key' => 'recibido_pdv',
            'toolbar_titulo' => 'Distribución mayorista → punto de venta',
            'recepcion_titulo' => 'Recepción en punto de venta',
            'recepcion_espera_info' => 'El transportista completa condiciones, llegada e incidentes. Le avisaremos cuando pueda firmar la recepción en tienda.',
            'destino_label' => $nombrePdv,
            'destino_articulo' => 'el punto de venta',
            'origen_salida' => 'centro mayorista',
            'tipo_tiempo_real' => 'distribucion',
            'firma_recepcion_titulo' => 'Firma de recepción en punto de venta',
            'firma_recepcion_hint' => 'Firma del minorista o responsable que recibe la mercadería en tienda.',
            'mensaje_finalizar' => '¿Confirma finalizar la entrega? Se ingresará el inventario al punto de venta.',
            'recibido_mensaje' => 'Recibido en punto de venta correctamente.',
            'documento_mensaje' => 'La entrega fue recibida en el punto de venta y el comprobante quedó registrado.',
            'llegada_titulo' => 'Confirmar llegada al punto de venta',
            'mensaje_espera_transportista' => 'No puede confirmar la llegada hasta estar físicamente en el punto de venta.',
            'espera_confirmacion_minorista_info' => 'El minorista del punto de venta debe confirmar que desea recibir este envío. Cuando lo haga, podrá registrar las condiciones del vehículo y continuar con el cierre operativo.',
        ];
    }
}
