<?php

namespace App\Support;

use App\Models\PedidoDistribucion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

final class PedidoDistribucionCatalogo
{
    public const ESTADO_PENDIENTE = 'pendiente';

    public const ESTADO_CONFIRMADO = 'confirmado';

    public const ESTADO_EN_TRANSITO = 'en_transito';

    public const ESTADO_RECIBIDO = 'recibido';

    public const ESTADO_RECHAZADO = 'rechazado';

    public const ESTADO_CANCELADO = 'cancelado';

    public static function generarNumeroSolicitud(): string
    {
        $fecha = Carbon::now()->format('Ymd');
        $ultimo = PedidoDistribucion::query()
            ->where('numero_solicitud', 'like', "PDV-{$fecha}-%")
            ->orderByDesc('pedidodistribucionid')
            ->value('numero_solicitud');

        $secuencia = 1;
        if ($ultimo && preg_match('/-(\d+)$/', $ultimo, $m)) {
            $secuencia = ((int) $m[1]) + 1;
        }

        return sprintf('PDV-%s-%04d', $fecha, $secuencia);
    }

    public static function pendienteAprobacionMayorista(PedidoDistribucion $pedido): bool
    {
        return $pedido->estado === self::ESTADO_PENDIENTE;
    }

    public static function pendienteAprobacionPlanta(PedidoDistribucion $pedido): bool
    {
        return self::pendienteAprobacionMayorista($pedido);
    }

    /** @deprecated Use pendienteAprobacionMayorista() */
    public static function pendienteAprobacionMinorista(PedidoDistribucion $pedido): bool
    {
        return self::pendienteAprobacionMayorista($pedido);
    }

    public static function puedeAceptarMayorista(PedidoDistribucion $pedido): bool
    {
        return self::pendienteAprobacionMayorista($pedido);
    }

    public static function puedeAceptarPlanta(PedidoDistribucion $pedido): bool
    {
        return self::puedeAceptarMayorista($pedido);
    }

    public static function puedeMarcarEnviado(PedidoDistribucion $pedido): bool
    {
        return $pedido->estado === self::ESTADO_CONFIRMADO;
    }

    /** Pedido aceptado y aún sin ruta ni transportista asignado. */
    public static function puedeDesignarTransportista(PedidoDistribucion $pedido): bool
    {
        return self::puedeMarcarEnviado($pedido) && $pedido->rutadistribucionid === null;
    }

    /** @deprecated Use puedeDesignarTransportista() */
    public static function puedeDespacharDirecto(PedidoDistribucion $pedido): bool
    {
        return self::puedeDesignarTransportista($pedido);
    }

    public static function tieneTransportistaDesignado(PedidoDistribucion $pedido): bool
    {
        return $pedido->estado === self::ESTADO_CONFIRMADO
            && ($pedido->rutadistribucionid !== null || $pedido->transportista_usuarioid !== null);
    }

    /** Mientras no haya salido en ruta ni cerrado el pedido. */
    public static function puedeEditarFlujoAntesDeRuta(PedidoDistribucion $pedido): bool
    {
        return in_array($pedido->estado, [self::ESTADO_PENDIENTE, self::ESTADO_CONFIRMADO], true);
    }

    public static function puedeReabrirRevision(PedidoDistribucion $pedido): bool
    {
        return $pedido->estado === self::ESTADO_CONFIRMADO
            && self::puedeEditarFlujoAntesDeRuta($pedido);
    }

    public static function puedeConfirmarRecepcion(PedidoDistribucion $pedido): bool
    {
        return $pedido->estado === self::ESTADO_EN_TRANSITO;
    }

    /** @return array<string, string> Etiquetas legibles para filtros (menos opciones técnicas). */
    public static function etiquetasFiltroEstado(): array
    {
        return [
            'revision' => 'En revisión',
            'preparacion' => 'Preparando envío',
            'camino' => 'En ruta',
            'recibido' => 'Recibido',
            'cerrado' => 'Cerrado',
        ];
    }

    /** @return list<string> */
    public static function estadosDeGrupoFiltro(string $grupo): array
    {
        return match ($grupo) {
            'revision' => [self::ESTADO_PENDIENTE],
            'preparacion' => [self::ESTADO_CONFIRMADO],
            'camino' => [self::ESTADO_EN_TRANSITO],
            'recibido' => [self::ESTADO_RECIBIDO],
            'cerrado' => [self::ESTADO_RECHAZADO, self::ESTADO_CANCELADO],
            default => [],
        };
    }

    public static function aplicarFiltroGrupoEstado(Builder $query, string $grupo): Builder
    {
        if ($grupo === 'camino') {
            return $query
                ->where('estado', self::ESTADO_EN_TRANSITO)
                ->whereHas('rutaDistribucion', fn (Builder $r) => $r
                    ->whereNotNull('simulacion_inicio_at')
                    ->where('estado', RutaDistribucionCatalogo::ESTADO_EN_RUTA));
        }

        if ($grupo === 'preparacion') {
            return $query->where(function (Builder $w) {
                $w->where('estado', self::ESTADO_CONFIRMADO)
                    ->orWhere(function (Builder $w2) {
                        $w2->where('estado', self::ESTADO_EN_TRANSITO)
                            ->where(function (Builder $w3) {
                                $w3->whereDoesntHave('rutaDistribucion')
                                    ->orWhereHas('rutaDistribucion', fn (Builder $r) => $r
                                        ->whereNull('simulacion_inicio_at')
                                        ->orWhere('estado', '!=', RutaDistribucionCatalogo::ESTADO_EN_RUTA));
                            });
                    });
            });
        }

        $estados = self::estadosDeGrupoFiltro($grupo);

        return $estados !== [] ? $query->whereIn('estado', $estados) : $query;
    }

    public static function estaEnRutaTiempoReal(PedidoDistribucion $pedido): bool
    {
        if ($pedido->estado !== self::ESTADO_EN_TRANSITO || $pedido->rutadistribucionid === null) {
            return false;
        }

        $ruta = $pedido->relationLoaded('rutaDistribucion')
            ? $pedido->rutaDistribucion
            : $pedido->rutaDistribucion()->first();

        return $ruta !== null && SimulacionRutaCatalogo::simulacionActivaDistribucion($ruta);
    }

    /** @return array<string, string> */
    public static function etiquetasEstado(): array
    {
        return [
            self::ESTADO_PENDIENTE => 'En revisión',
            self::ESTADO_CONFIRMADO => 'Preparando envío',
            self::ESTADO_EN_TRANSITO => 'En ruta',
            self::ESTADO_RECIBIDO => 'Recibido',
            self::ESTADO_RECHAZADO => 'Rechazado',
            self::ESTADO_CANCELADO => 'Cancelado',
        ];
    }

    public static function etiquetaEstado(?string $estado): string
    {
        return self::etiquetasEstado()[$estado ?? ''] ?? ucfirst(str_replace('_', ' ', (string) $estado));
    }

    public static function badgeBootstrapClase(array $badge): string
    {
        return match ($badge['clase'] ?? '') {
            'revision', 'asignado' => 'warning',
            'preparacion' => 'info',
            'ruta' => 'primary',
            'recibido' => 'success',
            'rechazado' => 'danger',
            'cancelado', 'neutral' => 'secondary',
            default => 'secondary',
        };
    }

    /** @return array{clase: string, etiqueta: string, icono: string} */
    public static function badgeEstado(PedidoDistribucion $pedido): array
    {
        return match ($pedido->estado) {
            self::ESTADO_PENDIENTE => ['clase' => 'revision', 'etiqueta' => 'En revisión', 'icono' => 'fa-hourglass-half'],
            self::ESTADO_CONFIRMADO => self::tieneTransportistaDesignado($pedido)
                ? ['clase' => 'asignado', 'etiqueta' => 'Transportista asignado', 'icono' => 'fa-user-check']
                : ['clase' => 'preparacion', 'etiqueta' => 'Preparando envío', 'icono' => 'fa-box-open'],
            self::ESTADO_EN_TRANSITO => self::estaEnRutaTiempoReal($pedido)
                ? ['clase' => 'ruta', 'etiqueta' => 'En ruta', 'icono' => 'fa-shipping-fast']
                : ['clase' => 'asignado', 'etiqueta' => 'Listo para salida', 'icono' => 'fa-truck-loading'],
            self::ESTADO_RECIBIDO => ['clase' => 'recibido', 'etiqueta' => 'Recibido', 'icono' => 'fa-check-circle'],
            self::ESTADO_RECHAZADO => ['clase' => 'rechazado', 'etiqueta' => 'Rechazado', 'icono' => 'fa-ban'],
            self::ESTADO_CANCELADO => ['clase' => 'cancelado', 'etiqueta' => 'Cancelado', 'icono' => 'fa-times-circle'],
            default => ['clase' => 'neutral', 'etiqueta' => self::etiquetaEstado($pedido->estado), 'icono' => 'fa-info-circle'],
        };
    }
}
