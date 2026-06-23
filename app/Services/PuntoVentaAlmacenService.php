<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\Insumo;
use App\Models\PuntoVenta;
use App\Models\TipoAlmacen;
use App\Models\UnidadMedida;
use App\Support\AlmacenAmbito;
use App\Support\AlmacenNombreCatalogo;

class PuntoVentaAlmacenService
{
    public function crearAlmacenParaPuntoVenta(PuntoVenta $puntoVenta): Almacen
    {
        if ($puntoVenta->almacenid) {
            $almacen = Almacen::query()->findOrFail($puntoVenta->almacenid);
            $this->sincronizarNombreAlmacen($puntoVenta, $almacen);

            return $almacen;
        }

        $tipoAlmacenId = TipoAlmacen::query()->value('tipoalmacenid');
        $unidadId = UnidadMedida::query()
            ->whereRaw("LOWER(TRIM(COALESCE(abreviatura, ''))) = ?", ['kg'])
            ->value('unidadmedidaid')
            ?? UnidadMedida::query()->value('unidadmedidaid');

        $almacen = Almacen::create([
            'nombre' => AlmacenNombreCatalogo::nombreParaPuntoVenta($puntoVenta),
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

    public function sincronizarNombreAlmacen(PuntoVenta $puntoVenta, ?Almacen $almacen = null): void
    {
        $almacen ??= $puntoVenta->almacen;
        if ($almacen === null) {
            return;
        }

        $nombre = AlmacenNombreCatalogo::nombreParaPuntoVenta($puntoVenta);
        if (trim((string) $almacen->nombre) !== $nombre) {
            $almacen->update(['nombre' => $nombre]);
        }
    }

    /** Normaliza nombres de almacén PDV ya existentes (p. ej. tras renombrado automático). */
    public function normalizarNombresAlmacenesExistentes(): int
    {
        $actualizados = 0;

        PuntoVenta::query()
            ->with('almacen')
            ->whereNotNull('almacenid')
            ->orderBy('puntoventaid')
            ->each(function (PuntoVenta $punto) use (&$actualizados): void {
                if ($punto->almacen === null) {
                    return;
                }

                $antes = trim((string) $punto->almacen->nombre);
                $this->sincronizarNombreAlmacen($punto, $punto->almacen);
                $punto->almacen->refresh();

                if ($antes !== trim((string) $punto->almacen->nombre)) {
                    $actualizados++;
                }
            });

        return $actualizados;
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

    /**
     * @return array{
     *     punto_nombre: string,
     *     almacen_nombre: string,
     *     capacidad_kg: float,
     *     ocupado_kg: float,
     *     disponible_kg: float,
     *     porcentaje: float,
     *     ingreso_kg: float,
     *     ocupado_despues_kg: float,
     *     disponible_despues_kg: float,
     *     porcentaje_despues: float,
     *     excede_capacidad: bool
     * }
     */
    public function resumenCapacidadPedido(PuntoVenta $puntoVenta, float $kgIngreso = 0): array
    {
        $this->crearAlmacenParaPuntoVenta($puntoVenta);
        $puntoVenta->loadMissing('almacen.unidadMedida');

        $almacen = $puntoVenta->almacen;
        if ($almacen === null) {
            throw new \InvalidArgumentException('No se pudo vincular el almacén del punto de venta.');
        }

        $capacidadService = app(AlmacenCapacidadService::class);
        $resumen = $capacidadService->resumen($almacen);
        $ingreso = max(0, $kgIngreso);
        $ocupadoDespues = $resumen['ocupado_kg'] + $ingreso;
        $capacidadKg = $resumen['capacidad_kg'];
        $disponibleDespues = max(0, $capacidadKg - $ocupadoDespues);
        $porcentajeDespues = $capacidadKg > 0
            ? min(100, round(($ocupadoDespues / $capacidadKg) * 100, 1))
            : 0;

        return [
            'punto_nombre' => $puntoVenta->nombre,
            'almacen_nombre' => $almacen->nombre,
            'capacidad_kg' => $capacidadKg,
            'ocupado_kg' => $resumen['ocupado_kg'],
            'disponible_kg' => $resumen['disponible_kg'],
            'porcentaje' => $resumen['porcentaje'],
            'ingreso_kg' => $ingreso,
            'ocupado_despues_kg' => $ocupadoDespues,
            'disponible_despues_kg' => $disponibleDespues,
            'porcentaje_despues' => $porcentajeDespues,
            'excede_capacidad' => $ingreso > 0 && $ingreso > $resumen['disponible_kg'] + 0.001,
        ];
    }

    public function validarIngresoPedido(PuntoVenta $puntoVenta, float $kgIngreso): void
    {
        if ($kgIngreso <= 0) {
            return;
        }

        $this->crearAlmacenParaPuntoVenta($puntoVenta);
        $puntoVenta->loadMissing('almacen');

        if ($puntoVenta->almacen === null) {
            throw new \InvalidArgumentException('No se pudo vincular el almacén del punto de venta.');
        }

        app(AlmacenCapacidadService::class)->validarIngresoKg($puntoVenta->almacen, $kgIngreso);
    }
}
