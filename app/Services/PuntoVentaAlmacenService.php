<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\Insumo;
use App\Models\PuntoVenta;
use App\Models\TipoAlmacen;
use App\Models\UnidadMedida;
use App\Support\AlmacenAmbito;

class PuntoVentaAlmacenService
{
    public function crearAlmacenParaPuntoVenta(PuntoVenta $puntoVenta): Almacen
    {
        if ($puntoVenta->almacenid) {
            return Almacen::query()->findOrFail($puntoVenta->almacenid);
        }

        $tipoAlmacenId = TipoAlmacen::query()->value('tipoalmacenid');
        $unidadId = UnidadMedida::query()
            ->whereRaw("LOWER(TRIM(COALESCE(abreviatura, ''))) = ?", ['kg'])
            ->value('unidadmedidaid')
            ?? UnidadMedida::query()->value('unidadmedidaid');

        $almacen = Almacen::create([
            'nombre' => 'Almacén — '.$puntoVenta->nombre,
            'descripcion' => 'Inventario del punto de venta '.$puntoVenta->nombre,
            'ubicacion' => $puntoVenta->direccion,
            'capacidad' => 500,
            'unidadmedidaid' => $unidadId,
            'tipoalmacenid' => $tipoAlmacenId,
            'ambito' => AlmacenAmbito::PUNTO_VENTA,
            'activo' => true,
        ]);

        $puntoVenta->update(['almacenid' => $almacen->almacenid]);

        return $almacen;
    }

    /** @return \Illuminate\Support\Collection<int, Insumo> */
    public function insumosEnPuntoVenta(PuntoVenta $puntoVenta)
    {
        if (! $puntoVenta->almacenid) {
            return collect();
        }

        return Insumo::query()
            ->with(['unidadMedida', 'tipo'])
            ->where('almacenid', $puntoVenta->almacenid)
            ->orderBy('nombre')
            ->get();
    }
}
