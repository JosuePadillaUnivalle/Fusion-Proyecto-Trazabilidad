<?php

namespace App\Support;

use App\Models\PedidoDistribucion;
use App\Models\Usuario;

final class MinoristaLoginNotificacion
{
    /**
     * Envíos iniciados por mayorista que el punto de venta debe confirmar o recibir.
     *
     * @return list<array{clave: string, codigo: string, producto: string, mayorista: string, url: string}>
     */
    public static function enviosPendientesConfirmacion(Usuario $user): array
    {
        if (! UsuarioRol::esMinorista($user)) {
            return [];
        }

        $items = PuntoVentaAccess::scopePedidosDelUsuario(PedidoDistribucion::query(), $user)
            ->with(['detalles.insumo', 'puntoVenta', 'almacenMayoristaOrigen.responsable'])
            ->whereIn('estado', [
                PedidoDistribucionCatalogo::ESTADO_CONFIRMADO,
                PedidoDistribucionCatalogo::ESTADO_EN_TRANSITO,
            ])
            ->orderByDesc('fechapedido')
            ->limit(12)
            ->get()
            ->filter(fn (PedidoDistribucion $p) => PedidoDistribucionCatalogo::pendienteConfirmacionMinorista($p)
                || ($p->envio_iniciado_mayorista && $p->fecha_confirmacion_minorista === null))
            ->map(function (PedidoDistribucion $pedido) {
                $det = $pedido->detalles->first();
                $resp = $pedido->almacenMayoristaOrigen?->responsable;
                $mayorista = $resp
                    ? trim(($resp->nombre ?? '').' '.($resp->apellido ?? ''))
                    : ($pedido->almacenMayoristaOrigen?->nombre ?? 'Mayorista');

                return [
                    'clave' => 'pdv_pedido:'.(int) $pedido->pedidodistribucionid,
                    'codigo' => $pedido->numero_solicitud ?? '#'.$pedido->pedidodistribucionid,
                    'producto' => $det?->producto_nombre ?? $det?->insumo?->nombre ?? 'Producto',
                    'mayorista' => $mayorista !== '' ? $mayorista : 'Mayorista',
                    'url' => route('punto-venta.pedidos.show', ['pedido' => $pedido, 'ctx' => 'pdv']),
                ];
            })
            ->values()
            ->all();

        return LoginNotificacionAlcance::filtrarPendientes(
            LoginNotificacionAlcance::MINORISTA,
            (int) $user->usuarioid,
            $items
        );
    }
}
