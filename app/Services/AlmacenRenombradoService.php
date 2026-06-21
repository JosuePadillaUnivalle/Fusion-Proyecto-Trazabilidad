<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\AlmacenMovimiento;
use App\Models\Pedido;
use App\Models\PedidoDestino;
use App\Support\AlmacenNombreCatalogo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class AlmacenRenombradoService
{
    /** @return array{actualizados: int, mapa: array<int, array{anterior: string, nuevo: string}>} */
    public function normalizarTodos(): array
    {
        if (! Schema::hasTable('almacen')) {
            return ['actualizados' => 0, 'mapa' => []];
        }

        $almacenes = Almacen::query()->orderBy('almacenid')->get();
        $mapa = [];

        foreach ($almacenes as $almacen) {
            $anterior = trim((string) $almacen->nombre);
            $nuevo = AlmacenNombreCatalogo::nombreDesdeRegistro($almacen);
            if ($anterior === $nuevo) {
                continue;
            }
            $mapa[(int) $almacen->almacenid] = ['anterior' => $anterior, 'nuevo' => $nuevo];
        }

        if ($mapa === []) {
            return ['actualizados' => 0, 'mapa' => []];
        }

        DB::transaction(function () use ($almacenes, $mapa) {
            foreach ($almacenes as $almacen) {
                if (! isset($mapa[(int) $almacen->almacenid])) {
                    continue;
                }
                Almacen::query()
                    ->where('almacenid', $almacen->almacenid)
                    ->update(['nombre' => '__tmp_alm_'.$almacen->almacenid]);
            }

            foreach ($mapa as $almacenId => $cambio) {
                Almacen::query()
                    ->where('almacenid', $almacenId)
                    ->update(['nombre' => $cambio['nuevo']]);
            }

            $this->sincronizarReferencias($mapa);
        });

        return ['actualizados' => count($mapa), 'mapa' => $mapa];
    }

    /** @param array<int, array{anterior: string, nuevo: string}> $mapa */
    private function sincronizarReferencias(array $mapa): void
    {
        if (Schema::hasTable('pedido_destino')) {
            foreach ($mapa as $almacenId => $cambio) {
                PedidoDestino::query()
                    ->where('almacen_origenid', $almacenId)
                    ->update(['almacen_origen_nombre' => $cambio['nuevo']]);
                PedidoDestino::query()
                    ->where('almacen_destinoid', $almacenId)
                    ->update(['almacen_destino_nombre' => $cambio['nuevo']]);
            }
        }

        if (Schema::hasTable('pedido')) {
            foreach ($mapa as $cambio) {
                if ($cambio['anterior'] === '') {
                    continue;
                }
                Pedido::query()
                    ->where('origen_direccion', $cambio['anterior'])
                    ->update(['origen_direccion' => $cambio['nuevo']]);
            }
        }

        if (Schema::hasTable('almacen_movimiento')) {
            foreach ($mapa as $cambio) {
                $this->reemplazarTextoEnColumna('almacen_movimiento', 'destino_motivo', $cambio['anterior'], $cambio['nuevo']);
                $this->reemplazarTextoEnColumna('almacen_movimiento', 'observaciones', $cambio['anterior'], $cambio['nuevo']);
                $this->reemplazarTextoEnColumna('almacen_movimiento', 'referencia', $cambio['anterior'], $cambio['nuevo']);
            }
        }

        $this->reemplazarTextoLibreEnTablas($mapa);
    }

    /** @param array<int, array{anterior: string, nuevo: string}> $mapa */
    private function reemplazarTextoLibreEnTablas(array $mapa): void
    {
        $pares = collect($mapa)
            ->sortByDesc(fn ($c) => mb_strlen($c['anterior']))
            ->values()
            ->all();

        $tablasColumnas = [
            ['pedido', 'origen_direccion'],
            ['pedido', 'observaciones'],
            ['pedido_distribucion', 'observaciones'],
        ];

        foreach ($tablasColumnas as [$tabla, $columna]) {
            if (! Schema::hasTable($tabla) || ! Schema::hasColumn($tabla, $columna)) {
                continue;
            }
            foreach ($pares as $cambio) {
                if ($cambio['anterior'] === '' || $cambio['anterior'] === $cambio['nuevo']) {
                    continue;
                }
                $this->reemplazarTextoEnColumna($tabla, $columna, $cambio['anterior'], $cambio['nuevo']);
            }
        }
    }

    private function reemplazarTextoEnColumna(string $tabla, string $columna, string $anterior, string $nuevo): void
    {
        if ($anterior === '' || $anterior === $nuevo) {
            return;
        }

        DB::table($tabla)
            ->where($columna, $anterior)
            ->update([$columna => $nuevo]);

        $pk = $this->primaryKeyDeTabla($tabla);
        if ($pk === null) {
            return;
        }

        DB::table($tabla)
            ->where($columna, 'like', '%'.$anterior.'%')
            ->where($columna, '!=', $nuevo)
            ->orderBy($pk)
            ->chunkById(200, function ($filas) use ($tabla, $columna, $pk, $anterior, $nuevo) {
                foreach ($filas as $fila) {
                    $texto = str_replace($anterior, $nuevo, (string) ($fila->{$columna} ?? ''));
                    DB::table($tabla)->where($pk, $fila->{$pk})->update([$columna => $texto]);
                }
            }, $pk);
    }

    private function primaryKeyDeTabla(string $tabla): ?string
    {
        return match ($tabla) {
            'almacen_movimiento' => 'almacen_movimientoid',
            'pedido' => 'pedidoid',
            'pedido_distribucion' => 'pedidodistribucionid',
            default => null,
        };
    }
}
