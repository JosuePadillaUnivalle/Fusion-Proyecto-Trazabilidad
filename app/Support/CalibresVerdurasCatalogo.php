<?php

namespace App\Support;

use App\Models\CatalogoTamanoConteo;
use App\Models\Insumo;
use App\Models\TipoEmpaque;
use Illuminate\Support\Facades\Schema;

/**
 * Calibres logísticos por verdura — sincroniza catálogo y evita duplicar registros.
 */
final class CalibresVerdurasCatalogo
{
    /** @return list<array{patron: string, empaque: string, calibres: list<array{nombre: string, conteo: int, peso: float}>}> */
    private static function plantillas(): array
    {
        return [
            ['patron' => '%tomate%', 'empaque' => 'Caja de cartón', 'calibres' => [
                ['nombre' => 'Pequeño (80-100 g)', 'conteo' => 258, 'peso' => 0.089],
                ['nombre' => 'Mediano (120-130 g)', 'conteo' => 184, 'peso' => 0.125],
                ['nombre' => 'Grande (160-180 g)', 'conteo' => 136, 'peso' => 0.169],
            ]],
            ['patron' => '%zanahoria%', 'empaque' => 'Caja de cartón', 'calibres' => [
                ['nombre' => 'Mediana (80-100 g)', 'conteo' => 120, 'peso' => 0.090],
                ['nombre' => 'Grande (120-150 g)', 'conteo' => 80, 'peso' => 0.135],
            ]],
            ['patron' => '%papa%', 'empaque' => 'Caja de cartón', 'calibres' => [
                ['nombre' => 'Mediana (150-200 g)', 'conteo' => 50, 'peso' => 0.175],
                ['nombre' => 'Grande (250-300 g)', 'conteo' => 35, 'peso' => 0.275],
            ]],
            ['patron' => '%lechuga%', 'empaque' => 'Canasta', 'calibres' => [
                ['nombre' => 'Unidad estándar (300-400 g)', 'conteo' => 24, 'peso' => 0.350],
                ['nombre' => 'Unidad grande (500-600 g)', 'conteo' => 18, 'peso' => 0.550],
            ]],
            ['patron' => '%cebolla%', 'empaque' => 'Caja de cartón', 'calibres' => [
                ['nombre' => 'Pequeña (80-100 g)', 'conteo' => 100, 'peso' => 0.090],
                ['nombre' => 'Mediana (120-150 g)', 'conteo' => 70, 'peso' => 0.135],
            ]],
            ['patron' => '%brócoli%', 'empaque' => 'Caja de cartón', 'calibres' => [
                ['nombre' => '14 coronas — caja', 'conteo' => 14, 'peso' => 0.500],
            ]],
            ['patron' => '%brocoli%', 'empaque' => 'Caja de cartón', 'calibres' => [
                ['nombre' => '14 coronas — caja', 'conteo' => 14, 'peso' => 0.500],
            ]],
            ['patron' => '%pepino%', 'empaque' => 'Caja de cartón', 'calibres' => [
                ['nombre' => 'Mediano (200-250 g)', 'conteo' => 40, 'peso' => 0.225],
            ]],
            ['patron' => '%piment%', 'empaque' => 'Caja de cartón', 'calibres' => [
                ['nombre' => 'Mediano (150-180 g)', 'conteo' => 60, 'peso' => 0.165],
            ]],
            ['patron' => '%espinaca%', 'empaque' => 'Bandeja', 'calibres' => [
                ['nombre' => 'Manojo estándar (250 g)', 'conteo' => 12, 'peso' => 0.250],
            ]],
        ];
    }

    public static function sincronizarParaInsumo(int $insumoId): void
    {
        if (! Schema::hasTable('catalogo_tamano_conteo')) {
            return;
        }

        $insumo = Insumo::query()->find($insumoId);
        if ($insumo === null) {
            return;
        }

        $yaTiene = CatalogoTamanoConteo::query()
            ->where('insumoid', $insumoId)
            ->where('activo', true)
            ->exists();

        if ($yaTiene) {
            return;
        }

        $nombre = mb_strtolower(trim($insumo->nombre));
        foreach (self::plantillas() as $grupo) {
            $patron = str_replace('%', '', strtolower($grupo['patron']));
            if (! str_contains($nombre, $patron)) {
                continue;
            }

            self::crearCalibresGrupo($insumoId, $grupo);

            return;
        }

        self::crearCalibreGenerico($insumoId);
    }

    /** @return int|null insumoid sincronizado */
    public static function sincronizarParaNombreCultivo(string $cultivoNombre): ?int
    {
        $insumo = PedidoCatalogo::insumoPorNombreCultivo($cultivoNombre);
        if ($insumo === null) {
            return null;
        }

        self::sincronizarParaInsumo((int) $insumo->insumoid);

        return (int) $insumo->insumoid;
    }

    /**
     * @param  array{patron: string, empaque: string, calibres: list<array{nombre: string, conteo: int, peso: float}>}  $grupo
     */
    private static function crearCalibresGrupo(int $insumoId, array $grupo): void
    {
        $empaque = TipoEmpaque::query()->where('nombre', $grupo['empaque'])->first();

        foreach ($grupo['calibres'] as $cal) {
            CatalogoTamanoConteo::query()->updateOrCreate(
                [
                    'insumoid' => $insumoId,
                    'nombre' => $cal['nombre'],
                ],
                [
                    'conteo_por_empaque' => $cal['conteo'],
                    'peso_promedio_kg' => $cal['peso'],
                    'tipoempaqueid' => $empaque?->tipoempaqueid,
                    'activo' => true,
                ]
            );
        }
    }

    private static function crearCalibreGenerico(int $insumoId): void
    {
        CatalogoTamanoConteo::query()->updateOrCreate(
            [
                'insumoid' => $insumoId,
                'nombre' => 'Estándar (1 kg)',
            ],
            [
                'conteo_por_empaque' => 1,
                'peso_promedio_kg' => 1.0,
                'activo' => true,
            ]
        );
    }
}
