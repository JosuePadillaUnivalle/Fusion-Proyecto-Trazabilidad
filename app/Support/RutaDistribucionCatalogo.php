<?php

namespace App\Support;

use App\Models\RutaDistribucion;
use Illuminate\Support\Carbon;

final class RutaDistribucionCatalogo
{
    public const ESTADO_PLANIFICADA = 'planificada';

    public const ESTADO_EN_RUTA = 'en_ruta';

    public const ESTADO_COMPLETADA = 'completada';

    public const ESTADO_CANCELADA = 'cancelada';

    public const PARADA_CARGA_PLANTA = 'carga_planta';

    public const PARADA_ENTREGA_PDV = 'entrega_pdv';

    public static function generarCodigo(): string
    {
        $fecha = Carbon::now()->format('Ymd');
        $ultimo = RutaDistribucion::query()
            ->where('codigo', 'like', "RD-{$fecha}-%")
            ->orderByDesc('rutadistribucionid')
            ->value('codigo');

        $secuencia = 1;
        if ($ultimo && preg_match('/-(\d+)$/', $ultimo, $m)) {
            $secuencia = ((int) $m[1]) + 1;
        }

        return sprintf('RD-%s-%04d', $fecha, $secuencia);
    }

    /** @return array<string, string> */
    public static function etiquetasEstado(): array
    {
        return [
            self::ESTADO_PLANIFICADA => 'Planificada',
            self::ESTADO_EN_RUTA => 'En ruta',
            self::ESTADO_COMPLETADA => 'Completada',
            self::ESTADO_CANCELADA => 'Cancelada',
        ];
    }

    public static function etiquetaEstado(?string $estado): string
    {
        return self::etiquetasEstado()[$estado ?? ''] ?? ucfirst(str_replace('_', ' ', (string) $estado));
    }

    /** @return array{clase: string, etiqueta: string} */
    public static function badgeEstado(RutaDistribucion $ruta): array
    {
        return match ($ruta->estado) {
            self::ESTADO_PLANIFICADA => ['clase' => 'info', 'etiqueta' => 'Planificada'],
            self::ESTADO_EN_RUTA => ['clase' => 'primary', 'etiqueta' => 'En ruta'],
            self::ESTADO_COMPLETADA => ['clase' => 'success', 'etiqueta' => 'Completada'],
            self::ESTADO_CANCELADA => ['clase' => 'secondary', 'etiqueta' => 'Cancelada'],
            default => ['clase' => 'secondary', 'etiqueta' => self::etiquetaEstado($ruta->estado)],
        };
    }
}
