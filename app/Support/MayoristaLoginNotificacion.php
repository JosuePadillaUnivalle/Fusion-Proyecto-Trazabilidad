<?php

namespace App\Support;

use App\Models\PedidoDistribucion;
use App\Models\RutaDistribucion;
use App\Models\Usuario;
use App\Services\RecepcionPlantaMayoristaService;

final class MayoristaLoginNotificacion
{
    /**
     * Solicitudes de PDV y recepciones de planta pendientes de acción del mayorista.
     *
     * @return list<array{clave: string, codigo: string, url: string, producto: string, minorista: string, cantidad: string, tipo: string}>
     */
    public static function nuevasSolicitudesDesdeLogin(Usuario $user, ?\Carbon\Carbon $ultimoLoginPrevio = null): array
    {
        if (! UsuarioRol::puedeGestionarDistribucionMayorista($user) || UsuarioRol::esMinorista($user)) {
            return [];
        }

        $items = [];

        self::agregarSolicitudesPdv($user, $items);
        self::agregarRecepcionesPlanta($user, $items);

        return LoginNotificacionAlcance::filtrarPendientes(
            LoginNotificacionAlcance::MAYORISTA,
            (int) $user->usuarioid,
            collect($items)->unique('clave')->values()->all()
        );
    }

    /** @param  list<array<string, string>>  $items */
    private static function agregarSolicitudesPdv(Usuario $user, array &$items): void
    {
        $almacenesIds = MayoristaAccess::idsAlmacenesOperados($user);
        if ($almacenesIds === []) {
            return;
        }

        PedidoDistribucion::query()
            ->whereIn('almacen_mayorista_origenid', $almacenesIds)
            ->where('estado', PedidoDistribucionCatalogo::ESTADO_PENDIENTE)
            ->with(['detalles.insumo.unidadMedida', 'puntoVenta.minorista'])
            ->orderByDesc('fechapedido')
            ->limit(10)
            ->get()
            ->each(function (PedidoDistribucion $pedido) use (&$items) {
                if (! PedidoDistribucionCatalogo::pendienteAprobacionMayorista($pedido)) {
                    return;
                }

                $det = $pedido->detalles->first();
                $minorista = $pedido->puntoVenta?->minorista;
                $nombreMinorista = $minorista
                    ? trim(($minorista->nombre ?? '').' '.($minorista->apellido ?? ''))
                    : 'Punto de venta';
                $unidad = $det?->insumo?->unidadMedida?->simbolo
                    ?? $det?->insumo?->unidadMedida?->nombre
                    ?? 'u.';

                $items[] = [
                    'clave' => 'pedido:'.(int) $pedido->pedidodistribucionid,
                    'codigo' => $pedido->numero_solicitud ?? '#'.$pedido->pedidodistribucionid,
                    'url' => route('punto-venta.pedidos.show', ['pedido' => $pedido, 'ctx' => 'mayorista']),
                    'producto' => $det?->producto_nombre ?? $det?->insumo?->nombre ?? 'Producto',
                    'minorista' => $nombreMinorista !== '' ? $nombreMinorista : 'Punto de venta',
                    'cantidad' => $det
                        ? number_format((float) $det->cantidad, 0).' '.$unidad
                        : '—',
                    'tipo' => 'solicitud_pdv',
                ];
            });
    }

    /** @param  list<array<string, string>>  $items */
    private static function agregarRecepcionesPlanta(Usuario $user, array &$items): void
    {
        $recepcion = app(RecepcionPlantaMayoristaService::class);
        if (! $recepcion->esVistaMayorista($user)) {
            return;
        }

        $recepcion->queryListado($user, RecepcionPlantaMayoristaService::FILTRO_ESPERANDO_FIRMA)
            ->with(['detallesTraslado.insumo', 'almacenMayoristaDestino'])
            ->limit(8)
            ->get()
            ->each(function (RutaDistribucion $ruta) use (&$items, $recepcion) {
                $estado = $recepcion->estadoRecepcion($ruta);
                if (! ($estado['puede_firmar'] ?? false)) {
                    return;
                }

                $detalle = $ruta->detallesTraslado?->first();
                $items[] = [
                    'clave' => 'traslado:'.(int) $ruta->rutadistribucionid,
                    'codigo' => $ruta->codigo ?? 'Traslado #'.$ruta->rutadistribucionid,
                    'url' => route('almacen-mayorista.traslados-planta.cierre.panel', $ruta),
                    'producto' => $detalle?->insumo?->nombre ?? $detalle?->producto_nombre ?? 'Producto de planta',
                    'minorista' => $ruta->almacenMayoristaDestino?->nombre ?? 'Su almacén',
                    'cantidad' => 'Firma de recepción',
                    'tipo' => 'recepcion_planta',
                ];
            });
    }
}
