<?php

namespace App\Support;

use App\Models\PedidoDistribucion;
use App\Models\Usuario;
use Carbon\Carbon;

final class MayoristaLoginNotificacion
{
    /**
     * Solicitudes de minoristas recién recibidas que aún no se notificaron en modal al mayorista.
     *
     * @return list<array{clave: string, codigo: string, url: string, producto: string, minorista: string, cantidad: string}>
     */
    public static function nuevasSolicitudesDesdeLogin(Usuario $user, ?Carbon $ultimoLoginPrevio): array
    {
        if (! UsuarioRol::puedeGestionarDistribucionMayorista($user) || UsuarioRol::esMinorista($user)) {
            return [];
        }

        $almacenesIds = MayoristaAccess::idsAlmacenesOperados($user);
        if ($almacenesIds === []) {
            return [];
        }

        $items = [];

        PedidoDistribucion::query()
            ->whereIn('almacen_mayorista_origenid', $almacenesIds)
            ->where('estado', PedidoDistribucionCatalogo::ESTADO_PENDIENTE)
            ->with(['detalles.insumo.unidadMedida', 'puntoVenta.minorista'])
            ->orderByDesc('fechapedido')
            ->limit(10)
            ->get()
            ->each(function (PedidoDistribucion $pedido) use (&$items, $ultimoLoginPrevio) {
                if (! PedidoDistribucionCatalogo::pendienteAprobacionMayorista($pedido)) {
                    return;
                }

                if (! self::solicitudEsNueva($pedido->fechapedido, $ultimoLoginPrevio)) {
                    return;
                }

                $det = $pedido->detalles->first();
                $minorista = $pedido->puntoVenta?->minorista;
                $nombreMinorista = $minorista
                    ? trim(($minorista->nombre ?? '').' '.($minorista->apellido ?? ''))
                    : 'Minorista';
                $unidad = $det?->insumo?->unidadMedida?->simbolo
                    ?? $det?->insumo?->unidadMedida?->nombre
                    ?? 'u.';

                $items[] = [
                    'clave' => MayoristaPedidoNotificacionVista::clavePedido((int) $pedido->pedidodistribucionid),
                    'codigo' => $pedido->numero_solicitud ?? '#'.$pedido->pedidodistribucionid,
                    'url' => route('punto-venta.pedidos.show', ['pedido' => $pedido, 'ctx' => 'mayorista']),
                    'producto' => $det?->producto_nombre ?? $det?->insumo?->nombre ?? 'Producto',
                    'minorista' => $nombreMinorista !== '' ? $nombreMinorista : 'Minorista',
                    'cantidad' => $det
                        ? number_format((float) $det->cantidad, 0).' '.$unidad
                        : '—',
                ];
            });

        return MayoristaPedidoNotificacionVista::filtrarPendientes((int) $user->usuarioid, $items);
    }

    private static function solicitudEsNueva(?Carbon $fechaPedido, ?Carbon $ultimoLoginPrevio): bool
    {
        if ($fechaPedido === null) {
            return false;
        }

        if ($ultimoLoginPrevio === null) {
            return $fechaPedido->greaterThanOrEqualTo(now()->subHours(24));
        }

        return $fechaPedido->greaterThan($ultimoLoginPrevio);
    }
}
