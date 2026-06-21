<?php

namespace App\Support;

use App\Models\LoteProduccionPedido;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LoteProduccionNombre
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
        if ($key === '') {
            return 1;
        }

        $max = 0;

        if (Schema::hasColumn('lote_produccion_pedido', 'producto')) {
            $nombres = LoteProduccionPedido::query()
                ->whereRaw('LOWER(TRIM(producto)) = ?', [$key])
                ->pluck('nombre');
        } else {
            $nombres = LoteProduccionPedido::query()
                ->whereRaw('LOWER(nombre) LIKE ?', [$key.' - lote %'])
                ->pluck('nombre');
        }

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

        if (! Schema::hasTable('lote_produccion_pedido')) {
            return $base;
        }

        $nombre = $base;
        $sufijo = 1;
        while (LoteProduccionPedido::query()->where('nombre', $nombre)->exists()) {
            $nombre = $base.' ('.$sufijo.')';
            $sufijo++;
        }

        return $nombre;
    }

    public static function productoDesdeLote(LoteProduccionPedido $lote): string
    {
        if (Schema::hasColumn('lote_produccion_pedido', 'producto') && filled($lote->producto)) {
            return self::normalizarProducto((string) $lote->producto);
        }

        if (preg_match('/^(.+?) - Lote \d+\s*$/i', (string) $lote->nombre, $m)) {
            return self::normalizarProducto($m[1]);
        }

        return self::normalizarProducto((string) $lote->nombre);
    }

    /**
     * @return list<string>
     */
    public static function productosDistintos(): array
    {
        if (! Schema::hasTable('lote_produccion_pedido')) {
            return [];
        }

        if (Schema::hasColumn('lote_produccion_pedido', 'producto')) {
            return LoteProduccionPedido::query()
                ->whereNotNull('producto')
                ->where('producto', '!=', '')
                ->distinct()
                ->orderBy('producto')
                ->pluck('producto')
                ->map(fn ($p) => self::normalizarProducto((string) $p))
                ->unique()
                ->values()
                ->all();
        }

        return LoteProduccionPedido::query()
            ->pluck('nombre')
            ->map(function ($nombre) {
                if (preg_match('/^(.+?) - Lote \d+\s*$/i', (string) $nombre, $m)) {
                    return self::normalizarProducto($m[1]);
                }

                return self::normalizarProducto((string) $nombre);
            })
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
