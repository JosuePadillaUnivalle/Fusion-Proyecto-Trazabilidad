<?php

namespace App\Support;

use App\Models\EnvioAsignacionMultiple;
use App\Models\RutaDistribucion;

final class TransporteIngresoCatalogo
{
    /** @var array<int, string> */
    private const ESTADOS_ENVIO_COMPLETADO = ['recibido_planta', 'entregado', 'entregada'];

    public static function envioCompletado(?EnvioAsignacionMultiple $envio): bool
    {
        if ($envio === null) {
            return false;
        }

        if ($envio->fecha_recepcion_planta !== null) {
            return true;
        }

        return in_array(strtolower(trim((string) ($envio->estado ?? ''))), self::ESTADOS_ENVIO_COMPLETADO, true);
    }

    public static function rutaCompletada(RutaDistribucion $ruta): bool
    {
        return $ruta->estado === RutaDistribucionCatalogo::ESTADO_COMPLETADA;
    }

    public static function formatearCosto(?float $costoBs): string
    {
        if ($costoBs === null) {
            return '—';
        }

        return number_format($costoBs, 2, ',', '.').' Bs';
    }
}
