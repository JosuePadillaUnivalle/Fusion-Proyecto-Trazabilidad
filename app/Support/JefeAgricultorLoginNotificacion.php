<?php

namespace App\Support;

use App\Models\EnvioAsignacionMultiple;
use App\Models\Pedido;
use App\Models\Usuario;

final class JefeAgricultorLoginNotificacion
{
    /**
     * Envíos agrícolas pendientes de confirmación hacia planta.
     *
     * @return list<array{clave: string, codigo: string, producto: string, url: string}>
     */
    public static function enviosPendientesConfirmacion(Usuario $user): array
    {
        if (! UsuarioRol::esJefeAgricultor($user) && ! UsuarioRol::esAdminGlobal($user)) {
            return [];
        }

        $items = [];

        Pedido::query()
            ->whereIn('estado', ['sin asignacion', 'pendiente'])
            ->with(['detalles', 'envioAsignacion'])
            ->orderByDesc('fechapedido')
            ->limit(12)
            ->get()
            ->each(function (Pedido $pedido) use (&$items) {
                if (! PedidoCatalogo::pendienteAprobacionAgricola($pedido)) {
                    return;
                }

                $detalle = $pedido->detalles->first();
                $envio = $pedido->envioAsignacion;
                $url = $envio
                    ? route('logistica.asignaciones.show', $envio)
                    : route('pedidos.show', $pedido);

                $items[] = [
                    'clave' => 'pedido:'.(int) $pedido->pedidoid,
                    'codigo' => $pedido->numero_solicitud ?? '#'.$pedido->pedidoid,
                    'producto' => $detalle?->cultivo_personalizado ?? 'Envío hacia planta',
                    'url' => $url,
                ];
            });

        EnvioAsignacionMultiple::query()
            ->whereHas('pedido', fn ($q) => $q->whereIn('estado', ['sin asignacion', 'pendiente']))
            ->with(['pedido.detalles'])
            ->orderByDesc('fecha_asignacion')
            ->limit(8)
            ->get()
            ->each(function (EnvioAsignacionMultiple $asignacion) use (&$items) {
                $pedido = $asignacion->pedido;
                if ($pedido === null || ! PedidoCatalogo::pendienteAprobacionAgricola($pedido)) {
                    return;
                }

                $clave = 'envio:'.(int) $asignacion->envioasignacionmultipleid;
                if (collect($items)->contains(fn ($row) => ($row['clave'] ?? '') === $clave)) {
                    return;
                }

                $items[] = [
                    'clave' => $clave,
                    'codigo' => $asignacion->externo_envio_id ?? $pedido->numero_solicitud ?? '#'.$asignacion->envioasignacionmultipleid,
                    'producto' => $pedido->detalles?->first()?->cultivo_personalizado ?? 'Confirmar envío a planta',
                    'url' => route('logistica.asignaciones.show', $asignacion),
                ];
            });

        return LoginNotificacionAlcance::filtrarPendientes(
            LoginNotificacionAlcance::JEFE_AGRICULTOR,
            (int) $user->usuarioid,
            collect($items)->unique('clave')->values()->all()
        );
    }
}
