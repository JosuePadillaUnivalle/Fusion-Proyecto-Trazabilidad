<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\AlmacenajeLoteProduccion;
use App\Models\Insumo;
use App\Models\LoteProduccionPedido;
use App\Models\TipoInsumo;
use App\Models\UnidadMedida;
use App\Support\InsumoCatalogo;
use App\Support\LoteProduccionNombre;
use App\Support\ProductoPlantaCatalogo;
use Illuminate\Support\Str;

class ProductoPlantaInventarioService
{
    public function __construct(
        private readonly AlmacenCapacidadService $capacidadService
    ) {}

    public function tipoProductoTerminadoId(): int
    {
        return (int) TipoInsumo::firstOrCreate(
            ['nombre' => 'Producto terminado'],
            ['nombre' => 'Producto terminado']
        )->tipoinsumoid;
    }

    public function sincronizarLoteAlmacenado(LoteProduccionPedido $lote, Almacen $almacen): Insumo
    {
        $this->sincronizarDesdeAlmacenajes((int) $almacen->almacenid);

        $nombre = LoteProduccionNombre::productoDesdeLote($lote);

        return Insumo::query()
            ->where('almacenid', $almacen->almacenid)
            ->whereRaw('LOWER(TRIM(nombre)) = ?', [Str::lower(trim($nombre))])
            ->where('tipoinsumoid', $this->tipoProductoTerminadoId())
            ->firstOrFail();
    }

    public function sincronizarDesdeAlmacenajes(?int $almacenId = null): void
    {
        $query = AlmacenajeLoteProduccion::query()
            ->with([
                'loteProduccionPedido.unidadMedida',
                'loteProduccionPedido.materiasPrimas.insumo.unidadMedida',
                'almacen',
            ])
            ->whereNull('fecha_retiro');

        if ($almacenId !== null) {
            $query->where('almacenid', $almacenId);
        }

        $agrupado = [];

        foreach ($query->get() as $ingreso) {
            $lote = $ingreso->loteProduccionPedido;
            $almacen = $ingreso->almacen;
            if ($lote === null || $almacen === null) {
                continue;
            }

            $nombre = LoteProduccionNombre::productoDesdeLote($lote);
            if ($nombre === '') {
                continue;
            }

            $resumen = ProductoPlantaCatalogo::resumenProduccion($lote, $this->capacidadService);
            $cantidad = (float) $ingreso->cantidad;
            if ($cantidad <= 0 && $resumen['cantidad'] > 0) {
                $cantidad = $resumen['cantidad'];
            }

            $clave = $almacen->almacenid.'|'.Str::lower($nombre);
            if (! isset($agrupado[$clave])) {
                $agrupado[$clave] = [
                    'almacen' => $almacen,
                    'nombre' => $nombre,
                    'cantidad' => 0.0,
                    'unidadmedidaid' => $this->resolverUnidadMedidaId($lote, $nombre),
                    'lote_ejemplo' => $lote,
                ];
            }

            $agrupado[$clave]['cantidad'] += $cantidad;
        }

        $tipoId = $this->tipoProductoTerminadoId();

        foreach ($agrupado as $grupo) {
            $insumo = Insumo::query()
                ->where('almacenid', $grupo['almacen']->almacenid)
                ->whereRaw('LOWER(TRIM(nombre)) = ?', [Str::lower(trim($grupo['nombre']))])
                ->first();

            if ($insumo !== null && (int) $insumo->tipoinsumoid !== $tipoId) {
                continue;
            }

            if ($insumo === null) {
                Insumo::create([
                    'nombre' => $grupo['nombre'],
                    'tipoinsumoid' => $tipoId,
                    'unidadmedidaid' => $grupo['unidadmedidaid'],
                    'stock' => $grupo['cantidad'],
                    'stockminimo' => InsumoCatalogo::UMBRAL_ALERTA_STOCK,
                    'descripcion' => 'Producto terminado de planta — sincronizado desde almacenaje de lotes.',
                    'almacenid' => $grupo['almacen']->almacenid,
                    'codigo_trazabilidad' => 'PT-'.now()->format('Ymd').'-'.strtoupper(substr(uniqid(), -6)),
                ]);
            } else {
                $insumo->update([
                    'unidadmedidaid' => $grupo['unidadmedidaid'],
                    'stock' => $grupo['cantidad'],
                ]);
            }
        }
    }

    private function resolverUnidadMedidaId(LoteProduccionPedido $lote, string $nombreProducto): int
    {
        $resuelto = ProductoPlantaCatalogo::resolverUnidadMedidaId(
            $nombreProducto,
            $lote->unidadmedidaid
        );

        if ($resuelto) {
            return (int) $resuelto;
        }

        if ($lote->unidadmedidaid) {
            return (int) $lote->unidadmedidaid;
        }

        $kgId = UnidadMedida::query()
            ->where(function ($q) {
                $q->whereRaw("LOWER(TRIM(COALESCE(abreviatura, ''))) = ?", ['kg'])
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%kilogramo%']);
            })
            ->value('unidadmedidaid');

        if ($kgId) {
            return (int) $kgId;
        }

        $fallback = UnidadMedida::query()->value('unidadmedidaid');

        if ($fallback) {
            return (int) $fallback;
        }

        throw new \RuntimeException('No hay unidades de medida configuradas para sincronizar productos de planta.');
    }
}
