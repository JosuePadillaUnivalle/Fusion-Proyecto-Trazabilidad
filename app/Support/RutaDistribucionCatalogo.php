<?php

namespace App\Support;

use App\Models\RutaDistribucion;
use Illuminate\Support\Carbon;

final class RutaDistribucionCatalogo
{
    public const ESTADO_PLANIFICADA = 'planificada';

    public const ESTADO_PENDIENTE_APROBACION = 'pendiente_aprobacion';

    public const ESTADO_EN_RUTA = 'en_ruta';

    public const ESTADO_COMPLETADA = 'completada';

    public const ESTADO_CANCELADA = 'cancelada';

    public const ESTADO_RECHAZADA = 'rechazada';

    public const PARADA_CARGA_PLANTA = 'carga_planta';

    public const PARADA_CARGA_MAYORISTA = 'carga_mayorista';

    public const PARADA_ENTREGA_MAYORISTA = 'entrega_mayorista';

    public const PARADA_ENTREGA_PDV = 'entrega_pdv';

    public const TIPO_RUTA_MAYORISTA_PDV = 'mayorista_pdv';

    public const TIPO_RUTA_PLANTA_MAYORISTA = 'planta_mayorista';

    public static function esTrasladoPlantaMayorista(RutaDistribucion $ruta): bool
    {
        return ($ruta->tipo_ruta ?? self::TIPO_RUTA_MAYORISTA_PDV) === self::TIPO_RUTA_PLANTA_MAYORISTA;
    }

    public static function pendienteAprobacionMayorista(RutaDistribucion $ruta): bool
    {
        return self::esTrasladoPlantaMayorista($ruta)
            && $ruta->estado === self::ESTADO_PENDIENTE_APROBACION;
    }

    public static function puedeAceptarMayorista(RutaDistribucion $ruta): bool
    {
        return self::pendienteAprobacionMayorista($ruta);
    }

    public static function puedeEmpezarTrasladoPlanta(RutaDistribucion $ruta): bool
    {
        return self::esTrasladoPlantaMayorista($ruta)
            && $ruta->estado === self::ESTADO_PLANIFICADA;
    }

    public static function generarCodigoTraslado(): string
    {
        $fecha = Carbon::now()->format('Ymd');
        $ultimo = RutaDistribucion::query()
            ->where('codigo', 'like', "TPM-{$fecha}-%")
            ->orderByDesc('rutadistribucionid')
            ->value('codigo');

        $secuencia = 1;
        if ($ultimo && preg_match('/-(\d+)$/', $ultimo, $m)) {
            $secuencia = ((int) $m[1]) + 1;
        }

        return sprintf('TPM-%s-%04d', $fecha, $secuencia);
    }

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
            self::ESTADO_PENDIENTE_APROBACION => 'Pendiente de aprobación',
            self::ESTADO_PLANIFICADA => 'Planificada',
            self::ESTADO_EN_RUTA => 'En ruta',
            self::ESTADO_COMPLETADA => 'Completada',
            self::ESTADO_CANCELADA => 'Cancelada',
            self::ESTADO_RECHAZADA => 'Rechazada',
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
            self::ESTADO_PENDIENTE_APROBACION => ['clase' => 'warning', 'etiqueta' => 'Pendiente aprobación'],
            self::ESTADO_PLANIFICADA => ['clase' => 'info', 'etiqueta' => 'Planificada'],
            self::ESTADO_EN_RUTA => ['clase' => 'primary', 'etiqueta' => 'En ruta'],
            self::ESTADO_COMPLETADA => ['clase' => 'success', 'etiqueta' => 'Completada'],
            self::ESTADO_RECHAZADA => ['clase' => 'danger', 'etiqueta' => 'Rechazada'],
            self::ESTADO_CANCELADA => ['clase' => 'secondary', 'etiqueta' => 'Cancelada'],
            default => ['clase' => 'secondary', 'etiqueta' => self::etiquetaEstado($ruta->estado)],
        };
    }
}
