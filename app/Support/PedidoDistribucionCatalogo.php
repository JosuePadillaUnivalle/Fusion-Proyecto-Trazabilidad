<?php

namespace App\Support;

use App\Models\PedidoDistribucion;
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

    public static function pendienteAprobacionPlanta(PedidoDistribucion $pedido): bool
    {
        return $pedido->estado === self::ESTADO_PENDIENTE;
    }

    /** @deprecated Use pendienteAprobacionPlanta() */
    public static function pendienteAprobacionMinorista(PedidoDistribucion $pedido): bool
    {
        return self::pendienteAprobacionPlanta($pedido);
    }

    public static function puedeAceptarPlanta(PedidoDistribucion $pedido): bool
    {
        return self::pendienteAprobacionPlanta($pedido);
    }

    public static function puedeMarcarEnviado(PedidoDistribucion $pedido): bool
    {
        return $pedido->estado === self::ESTADO_CONFIRMADO;
    }

    public static function puedeDespacharDirecto(PedidoDistribucion $pedido): bool
    {
        return self::puedeMarcarEnviado($pedido) && $pedido->rutadistribucionid === null;
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
            'camino' => 'En camino',
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

    /** @return array<string, string> */
    public static function etiquetasEstado(): array
    {
        return [
            self::ESTADO_PENDIENTE => 'En revisión',
            self::ESTADO_CONFIRMADO => 'Preparando envío',
            self::ESTADO_EN_TRANSITO => 'En camino',
            self::ESTADO_RECIBIDO => 'Recibido',
            self::ESTADO_RECHAZADO => 'Rechazado',
            self::ESTADO_CANCELADO => 'Cancelado',
        ];
    }

    public static function etiquetaEstado(?string $estado): string
    {
        return self::etiquetasEstado()[$estado ?? ''] ?? ucfirst(str_replace('_', ' ', (string) $estado));
    }

    /** @return array{clase: string, etiqueta: string} */
    public static function badgeEstado(PedidoDistribucion $pedido): array
    {
        return match ($pedido->estado) {
            self::ESTADO_PENDIENTE => ['clase' => 'warning', 'etiqueta' => 'En revisión'],
            self::ESTADO_CONFIRMADO => ['clase' => 'info', 'etiqueta' => 'Preparando envío'],
            self::ESTADO_EN_TRANSITO => ['clase' => 'primary', 'etiqueta' => 'En camino'],
            self::ESTADO_RECIBIDO => ['clase' => 'success', 'etiqueta' => 'Recibido'],
            self::ESTADO_RECHAZADO => ['clase' => 'danger', 'etiqueta' => 'Rechazado'],
            self::ESTADO_CANCELADO => ['clase' => 'secondary', 'etiqueta' => 'Cancelado'],
            default => ['clase' => 'secondary', 'etiqueta' => self::etiquetaEstado($pedido->estado)],
        };
    }
}
