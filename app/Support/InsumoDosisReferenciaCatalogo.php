<?php

namespace App\Support;

use App\Models\Insumo;

/**
 * Dosis de referencia por hectárea para fertilizantes y plaguicidas
 * cuando el insumo no tiene dosis configurada en inventario.
 */
class InsumoDosisReferenciaCatalogo
{
    /** @return array{por_ha: float, unidad: string, etiqueta_unidad: string}|null */
    public static function paraInsumo(Insumo $insumo): ?array
    {
        $insumo->loadMissing(['tipo', 'unidadMedida']);
        $nombre = mb_strtolower(trim($insumo->nombre ?? ''));
        $slug = InsumoCatalogo::slugFromNombreTipo($insumo->tipo?->nombre ?? '') ?? '';
        $umStock = mb_strtolower(trim($insumo->unidadMedida?->abreviatura ?? $insumo->unidadMedida?->nombre ?? 'kg'));

        if ($slug === 'fertilizantes') {
            if (str_contains($nombre, 'npk')) {
                return self::kg(250.0);
            }
            if (str_contains($nombre, 'urea')) {
                return self::kg(150.0);
            }
            if (str_contains($nombre, 'compost') || str_contains($nombre, 'abono') || str_contains($nombre, 'orgánico') || str_contains($nombre, 'organico')) {
                return self::kg(3000.0);
            }

            return self::kg(200.0);
        }

        if (in_array($slug, ['pesticidas', 'bioinsumo'], true)) {
            $esLitros = in_array($umStock, ['l', 'lt', 'litro', 'litros', 'litros.'], true)
                || str_contains($umStock, 'lit');

            if (str_contains($nombre, 'herbicida')) {
                return $esLitros ? self::litros(2.5) : self::kg(2.0);
            }
            if (str_contains($nombre, 'fungicida') || str_contains($nombre, 'insecticida') || str_contains($nombre, 'plaga')) {
                return $esLitros ? self::litros(1.5) : self::kg(1.5);
            }

            return $esLitros ? self::litros(2.0) : self::kg(2.0);
        }

        return null;
    }

    /** @return array{por_ha: float, unidad: string, etiqueta_unidad: string} */
    private static function kg(float $porHa): array
    {
        return [
            'por_ha' => $porHa,
            'unidad' => 'kg',
            'etiqueta_unidad' => 'kilogramos',
        ];
    }

    /** @return array{por_ha: float, unidad: string, etiqueta_unidad: string} */
    private static function litros(float $porHa): array
    {
        return [
            'por_ha' => $porHa,
            'unidad' => 'L',
            'etiqueta_unidad' => 'litros',
        ];
    }
}
