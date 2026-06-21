<?php

namespace App\Support;

use App\Models\Lote;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/** Nombres automáticos y únicos para lotes agrícolas (por cultivo/producto). */
final class LoteAgricolaNombre
{
    public static function normalizarProducto(string $producto): string
    {
        $limpio = preg_replace('/\s+/', ' ', trim($producto));

        return $limpio ?? trim($producto);
    }

    public static function formatear(string $producto, int $numero): string
    {
        return self::normalizarProducto($producto).' - Lote '.str_pad((string) $numero, 3, '0', STR_PAD_LEFT);
    }

    public static function siguienteNumero(string $producto): int
    {
        $key = Str::lower(self::normalizarProducto($producto));
        if ($key === '' || ! Schema::hasTable('lote')) {
            return 1;
        }

        $max = 0;
        $nombres = Lote::query()
            ->whereRaw('LOWER(nombre) LIKE ?', [$key.' - lote %'])
            ->pluck('nombre');

        foreach ($nombres as $nombre) {
            if (preg_match('/- Lote (\d+)\s*$/i', (string) $nombre, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return $max + 1;
    }

    public static function siguienteNombre(string $producto): string
    {
        $base = self::formatear($producto, self::siguienteNumero($producto));

        if (! Schema::hasTable('lote')) {
            return $base;
        }

        $nombre = $base;
        $sufijo = 1;
        while (Lote::query()->where('nombre', $nombre)->exists()) {
            $nombre = $base.' ('.$sufijo.')';
            $sufijo++;
        }

        return $nombre;
    }

    public static function productoDesdeInsumo(?int $insumoId): ?string
    {
        if (! $insumoId || ! Schema::hasTable('insumo')) {
            return null;
        }

        $nombre = \App\Models\Insumo::query()->where('insumoid', $insumoId)->value('nombre');

        return filled($nombre) ? self::normalizarProducto((string) $nombre) : null;
    }
}
