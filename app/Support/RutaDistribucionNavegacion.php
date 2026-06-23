<?php

namespace App\Support;

use App\Models\RutaDistribucion;
use App\Models\Usuario;
use Illuminate\Support\Carbon;

final class RutaDistribucionNavegacion
{
    public static function urlVer(RutaDistribucion $ruta, ?Usuario $user = null): string
    {
        $user ??= auth()->user();

        if ($ruta->esTrasladoPlantaMayorista()) {
            if (self::transportistaDebeUsarCierre($ruta, $user)) {
                return route('logistica.traslados-planta.cierre.panel', $ruta);
            }

            return route('logistica.traslados-planta.show', $ruta);
        }

        if (self::transportistaDebeUsarCierre($ruta, $user)) {
            return route('punto-venta.rutas.cierre.panel', $ruta);
        }

        return route('punto-venta.rutas.show', $ruta);
    }

    public static function transportistaDebeUsarCierre(RutaDistribucion $ruta, ?Usuario $user): bool
    {
        if ($user === null || ! UsuarioRol::esTransportista($user)) {
            return false;
        }

        if ((int) $ruta->transportista_usuarioid !== (int) $user->usuarioid) {
            return false;
        }

        if ($ruta->esTrasladoPlantaMayorista()) {
            return in_array($ruta->estado, [
                RutaDistribucionCatalogo::ESTADO_PLANIFICADA,
                RutaDistribucionCatalogo::ESTADO_EN_RUTA,
            ], true);
        }

        return in_array($ruta->estado, [
            RutaDistribucionCatalogo::ESTADO_PLANIFICADA,
            RutaDistribucionCatalogo::ESTADO_EN_RUTA,
        ], true);
    }

    public static function nombreAlmacenCarga(RutaDistribucion $ruta): string
    {
        if ($ruta->esTrasladoPlantaMayorista()) {
            return $ruta->almacenPlantaOrigen?->nombre ?? '—';
        }

        return $ruta->almacenOrigen?->nombre ?? '—';
    }

    public static function fechaSalida(RutaDistribucion $ruta): ?Carbon
    {
        return $ruta->simulacion_inicio_at ?? $ruta->fecha_salida;
    }

    public static function etiquetaParadaCarga(string $tipo): string
    {
        return match ($tipo) {
            RutaDistribucionCatalogo::PARADA_CARGA_PLANTA => 'Punto de carga — planta',
            RutaDistribucionCatalogo::PARADA_CARGA_MAYORISTA => 'Punto de carga — mayorista',
            default => 'Punto de carga',
        };
    }

    public static function resumenProducto(RutaDistribucion $ruta): string
    {
        if ($ruta->esTrasladoPlantaMayorista()) {
            $detalle = $ruta->detallesTraslado?->first();

            return $detalle?->insumo?->nombre
                ?? $detalle?->producto_nombre
                ?? 'Traslado planta → mayorista';
        }

        $primer = $ruta->pedidos?->first()?->detalles?->first();

        return $primer?->producto_nombre
            ?? $primer?->cultivo_personalizado
            ?? 'Distribución a punto de venta';
    }

    public static function volverUrl(RutaDistribucion $ruta, ?Usuario $user = null, string $rutaPrefijo = 'logistica.traslados-planta'): string
    {
        $user ??= auth()->user();
        $ruta->loadMissing('pedidos');

        if ($user !== null && UsuarioRol::esTransportista($user)) {
            return route('logistica.asignaciones.listado');
        }

        if ($ruta->esTrasladoPlantaMayorista()) {
            return route($rutaPrefijo.'.show', $ruta);
        }

        if ($user !== null && UsuarioRol::puedeGestionarDistribucionMayorista($user)) {
            $pedido = $ruta->pedidos->first();
            if ($pedido !== null) {
                return route('punto-venta.pedidos.show', ['pedido' => $pedido, 'ctx' => 'mayorista']);
            }

            return route('logistica.rutas-tiempo-real.index');
        }

        if ($user !== null && UsuarioRol::esMinorista($user)) {
            $pedido = $ruta->pedidos->first(
                fn ($p) => PuntoVentaAccess::puedeVerPedido($user, $p)
            );
            if ($pedido !== null) {
                return route('punto-venta.pedidos.show', ['pedido' => $pedido, 'paso' => 4]);
            }
        }

        if (UsuarioRol::puedePlanificarDistribucion($user)) {
            return route('punto-venta.rutas.index');
        }

        return route('logistica.rutas-tiempo-real.index');
    }
}
